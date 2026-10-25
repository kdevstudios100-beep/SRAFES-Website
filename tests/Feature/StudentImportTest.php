<?php

use App\Enum\UserRole;
use App\Filament\Pages\ImportStudents;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Section;
use App\Models\Student;
use App\Support\Academics\StudentImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->session = AcademicSession::factory()->startingIn(2026)->current()->create();
    $this->class5 = AcademicClass::factory()->create(['name' => 'Class 5', 'sort_order' => 5]);
    $this->section = Section::factory()->for($this->class5)->create(['name' => 'A']);
    $this->csvDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'srafes-import-test-'.bin2hex(random_bytes(4));
    mkdir($this->csvDir);
});

afterEach(function () {
    array_map('unlink', glob($this->csvDir.'/*'));
    rmdir($this->csvDir);
});

function csvFile(string $contents): string
{
    $path = test()->csvDir.DIRECTORY_SEPARATOR.bin2hex(random_bytes(4)).'.csv';
    file_put_contents($path, $contents);

    return $path;
}

it('checks a file without writing anything, then imports only the ready rows', function () {
    Student::factory()->create(['admission_number' => 'SR-1', 'first_name' => 'Existing']);
    Student::factory()->create(['admission_number' => 'SR-50', 'first_name' => 'Sara', 'last_name' => 'Ali', 'father_name' => 'Ali Khan', 'date_of_birth' => '2016-05-01']);

    $path = csvFile(implode("\n", [
        'Admission No,First Name,Last Name,Father Name,DOB,Class,Section,Roll No,Scholarship,Notes',
        'SR-10,Ayesha,Khan,Imran,21/03/2015,Class 5,A,12,yes,ignored',
        'SR-11,Bilal,,Tariq,2014-11-02,5,,,no,',                          // class by position, no section
        'sr-1,Copy,,,,Class 5,,,,',                                       // existing admission number (case-insensitive)
        'SR-10,Again,,,,Class 5,,,,',                                     // repeated in file
        'SR-12,Sara,Ali,Ali Khan,01/05/2016,Class 5,,,,',                 // same person as SR-50
        'SR-13,,,,,Class 5,,,,',                                          // missing first name
        'SR-14,Hamza,,,31/02/2015,Class 9,Z,,maybe,',                     // bad date, unknown class, bad flag
        'SR-15,Zara,,,,Class 5,C,,,',                                     // unknown section
        ',,,,,,,,,',                                                      // blank line is ignored
    ]));

    $import = app(StudentImport::class);
    $report = $import->analyze($path)->toArray();

    expect(Student::count())->toBe(2)
        ->and($report['fatal'])->toBeNull()
        ->and($report['total'])->toBe(8)
        ->and(array_column($report['ready'], 'admission_number'))->toBe(['SR-10', 'SR-11'])
        ->and(array_column($report['duplicates'], 'line'))->toBe([4, 5, 6])
        ->and(array_column($report['invalid'], 'line'))->toBe([7, 8, 9])
        ->and($report['ignored_columns'])->toBe(['notes'])
        ->and(implode(' ', $report['duplicates'][0]['messages']))->toContain('Existing')
        ->and(implode(' ', $report['duplicates'][1]['messages']))->toContain('line 2')
        ->and(implode(' ', $report['duplicates'][2]['messages']))->toContain('SR-50')
        ->and(implode(' ', $report['invalid'][1]['messages']))->toContain('not a valid date')->toContain('Class 9')->toContain('yes or no');

    $result = $import->import($path, $this->session);

    expect($result->imported)->toBe(2)
        ->and(Student::count())->toBe(4);

    $ayesha = Student::where('admission_number', 'SR-10')->sole();
    expect($ayesha->only(['first_name', 'last_name', 'father_name', 'date_of_birth', 'has_scholarship', 'is_active']))
        ->toBe(['first_name' => 'Ayesha', 'last_name' => 'Khan', 'father_name' => 'Imran', 'date_of_birth' => '2015-03-21', 'has_scholarship' => true, 'is_active' => true])
        ->and($ayesha->currentEnrollment->placement)->toBe('Class 5 - A')
        ->and($ayesha->currentEnrollment->roll_number)->toBe('12')
        ->and($ayesha->currentEnrollment->academic_session_id)->toBe($this->session->id)
        ->and(Student::where('admission_number', 'SR-11')->sole()->currentEnrollment->placement)->toBe('Class 5');
});

it('imports nothing twice: a second run finds every row a duplicate', function () {
    $path = csvFile("admission_number,first_name,class\nSR-1,Ali,Class 5\nSR-2,Bina,Class 5\n");

    app(StudentImport::class)->import($path, $this->session);
    $second = app(StudentImport::class)->import($path, $this->session);

    expect($second->imported)->toBe(0)
        ->and(count($second->duplicates()))->toBe(2)
        ->and(Student::count())->toBe(2);
});

it('rolls back the whole import if a row fails while saving', function () {
    $path = csvFile("admission_number,first_name,class\nSR-1,Ali,Class 5\nSR-2,Bina,Class 5\n");

    // Simulate a failure on the second student
    Student::creating(function (Student $student) {
        if ($student->admission_number === 'SR-2') {
            throw new RuntimeException('disk full');
        }
    });

    expect(fn () => app(StudentImport::class)->import($path, $this->session))->toThrow(RuntimeException::class);
    expect(Student::count())->toBe(0);
});

it('rejects files with missing columns, no rows or too many rows', function (string $contents, string $message) {
    config(['srafes.academics.import_max_rows' => 2]);

    $report = app(StudentImport::class)->analyze(csvFile($contents));

    expect($report->fatal)->toContain($message);
})->with([
    'missing column' => ["first_name,class\nAli,Class 5\n", 'admission_number'],
    'header only' => ["admission_number,first_name,class\n", 'no students'],
    'too many rows' => ["admission_number,first_name,class\n1,A,Class 5\n2,B,Class 5\n3,C,Class 5\n", 'more than 2'],
    'repeated column' => ["admission_number,first_name,class,class\n1,A,Class 5,Class 5\n", 'more than once'],
]);

it('reads Excel-style files with a byte order mark and semicolons', function () {
    $report = app(StudentImport::class)->analyze(csvFile("\xEF\xBB\xBFAdmission Number;First Name;Class\r\nSR-9;Noor;class 5\r\n"));

    expect(array_column($report->ready(), 'admission_number'))->toBe(['SR-9']);
});

it('runs the check-then-import flow on the admin page', function () {
    Storage::fake('local');
    $this->actingAs(userWithRole(UserRole::Principal));

    $page = Livewire::test(ImportStudents::class)
        ->set('data.academic_session_id', $this->session->id)
        ->set('data.file', UploadedFile::fake()->createWithContent('students.csv', "admission_number,first_name,class,section\nSR-1,Ali,Class 5,A\nSR-1,Twice,Class 5,A\n"))
        ->call('check')
        ->assertSee('Check complete')
        ->assertSee('Same admission number as line 2');

    expect(Student::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('imports'))->toHaveCount(1);

    $page->call('import')->assertSee('Import complete');

    expect(Student::sole()->currentEnrollment->placement)->toBe('Class 5 - A')
        ->and(Storage::disk('local')->allFiles('imports'))->toBeEmpty();
});

it('refuses the import page and its actions to staff', function () {
    $this->actingAs(userWithRole(UserRole::Staff));

    $this->get('/admin/import-students')->assertForbidden();
    Livewire::test(ImportStudents::class)->assertForbidden();
});

it('cannot be pointed at another file from the browser', function () {
    $this->actingAs(userWithRole(UserRole::Principal));

    expect(fn () => Livewire::test(ImportStudents::class)->set('storedPath', '../../.env'))
        ->toThrow(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
});
