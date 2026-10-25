<?php

use App\Enum\UserRole;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\RelationManagers\EnrollmentsRelationManager;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Support\Academics\Enrollments;
use App\Support\Academics\RollNumberTakenException;
use App\Support\Academics\StudentImport;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->session = AcademicSession::factory()->startingIn(2026)->current()->create();
    $this->class5 = AcademicClass::factory()->create(['name' => 'Class 5', 'sort_order' => 5]);
    $this->class6 = AcademicClass::factory()->create(['name' => 'Class 6', 'sort_order' => 6]);
    $this->sectionA = Section::factory()->for($this->class5)->create(['name' => 'A']);
    $this->sectionB = Section::factory()->for($this->class5)->create(['name' => 'B']);
    $this->enrollments = app(Enrollments::class);

    $this->place = fn (string $name, AcademicClass $class, ?Section $section, ?string $roll) => $this->enrollments->enroll(
        Student::factory()->create(['first_name' => $name, 'last_name' => '', 'is_active' => true]),
        $this->session, $class, $section, $roll, Carbon::parse('2026-04-01'),
    );

    $this->amna = ($this->place)('Amna', $this->class5, $this->sectionA, '1');
});

it('allows roll number 1 in every class and section', function () {
    ($this->place)('Bilal', $this->class5, $this->sectionB, '1');
    ($this->place)('Chand', $this->class6, null, '1');

    expect(Enrollment::where('roll_number', '1')->count())->toBe(3);
});

it('refuses a roll number already held in the same section', function () {
    expect(fn () => ($this->place)('Bilal', $this->class5, $this->sectionA, ' 1 '))
        ->toThrow(RollNumberTakenException::class, 'Roll number 1 in Class 5 - A already belongs to Amna');

    expect(Student::where('first_name', 'Bilal')->sole()->enrollments()->exists())->toBeFalse();
});

it('allows students without a roll number to share a section', function () {
    ($this->place)('Bilal', $this->class5, $this->sectionA, null);
    ($this->place)('Chand', $this->class5, $this->sectionA, '');

    expect($this->sectionA->enrollments()->count())->toBe(3);
});

it('frees a roll number once its holder leaves or moves', function () {
    $this->enrollments->withdraw($this->amna->student, Carbon::parse('2026-10-01'));

    ($this->place)('Bilal', $this->class5, $this->sectionA, '1');

    expect($this->sectionA->enrollments()->active()->sole()->student->first_name)->toBe('Bilal');
});

it('refuses a transfer that carries a taken roll number into the new section', function () {
    $bilal = ($this->place)('Bilal', $this->class5, $this->sectionB, '1');

    expect(fn () => $this->enrollments->transfer($bilal->student, $this->class5, $this->sectionA))
        ->toThrow(RollNumberTakenException::class, 'Amna');
    expect($bilal->fresh()->status->value)->toBe('active');

    $this->enrollments->transfer($bilal->student, $this->class5, $this->sectionA, '2');

    expect($bilal->student->currentEnrollment->only(['section_id', 'roll_number']))->toBe(['section_id' => $this->sectionA->id, 'roll_number' => '2']);
});

it('flags taken and repeated roll numbers when checking an import', function () {
    $path = tempnam(sys_get_temp_dir(), 'roll').'.csv';
    file_put_contents($path, implode("\n", [
        'admission_number,first_name,class,section,roll_number',
        'R-1,Bilal,Class 5,A,1',  // taken by Amna
        'R-2,Chand,Class 5,A,2',
        'R-3,Daud,Class 5,A,2',   // repeats line 3
        'R-4,Esha,Class 5,B,1',   // other section: fine
    ]));

    $report = app(StudentImport::class)->analyze($path, $this->session)->toArray();
    unlink($path);

    expect(array_column($report['ready'], 'admission_number'))->toBe(['R-2', 'R-4'])
        ->and(array_column($report['invalid'], 'line'))->toBe([2, 4])
        ->and(implode(' ', $report['invalid'][0]['messages']))->toContain('Roll number 1 in Class 5 - A already belongs to Amna')
        ->and($report['invalid'][1]['messages'])->toContain('Roll number 2 is also given on line 3 in the same class and section.');
});

it('shows the clash on the roll number field when creating a student', function () {
    $this->actingAs(userWithRole(UserRole::Staff));

    Livewire::test(CreateStudent::class)
        ->fillForm(['admission_number' => 'R-9', 'first_name' => 'Bilal', 'academic_class_id' => $this->class5->id, 'section_id' => $this->sectionA->id, 'roll_number' => '1'])
        ->call('create')
        ->assertHasFormErrors(['roll_number']);

    expect(Student::where('admission_number', 'R-9')->exists())->toBeFalse();
});

it('refuses correcting a roll number to one already taken', function () {
    $bilal = ($this->place)('Bilal', $this->class5, $this->sectionA, '2');
    $this->actingAs(userWithRole(UserRole::Staff));

    Livewire::test(EnrollmentsRelationManager::class, ['ownerRecord' => $bilal->student, 'pageClass' => ViewStudent::class])
        ->callTableAction('edit', $bilal, data: ['roll_number' => '1'])
        ->assertHasTableActionErrors(['roll_number']);

    Livewire::test(EnrollmentsRelationManager::class, ['ownerRecord' => $bilal->student, 'pageClass' => ViewStudent::class])
        ->callTableAction('edit', $bilal, data: ['roll_number' => ' 3 '])
        ->assertHasNoTableActionErrors();

    expect($bilal->fresh()->roll_number)->toBe('3');
});
