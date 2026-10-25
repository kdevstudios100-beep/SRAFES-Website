<?php

use App\Enum\EnrollmentStatus;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\ExaminationResult;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
 * Runs the data migrations against records shaped like the school's
 * existing data (classes without positions, students with only a class).
 */
function runMigration(string $name, string $method = 'up'): void
{
    (require database_path("migrations/{$name}.php"))->{$method}();
}

it('enrolls existing students in the current session without changing them', function () {
    Carbon::setTestNow('2026-10-25');

    $one = AcademicClass::create(['name' => 'Class One']);
    $two = AcademicClass::create(['name' => 'Class Two']);
    $twelve = AcademicClass::create(['name' => 'Class 12']);
    $nursery = AcademicClass::create(['name' => 'Nursery']);
    $active = Student::factory()->create(['academic_class_id' => $one->id, 'is_active' => true]);
    $inactive = Student::factory()->create(['academic_class_id' => $two->id, 'is_active' => false]);
    $unplaced = Student::factory()->create(['academic_class_id' => null]);
    $result = ExaminationResult::create(['student_id' => $active->id, 'academic_class_id' => $two->id, 'year' => '2025', 'total_marks' => 100, 'obtained_marks' => 35]);
    $before = DB::table('students')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    $resultBefore = (array) DB::table('examination_results')->first();

    runMigration('2026_10_25_100100_backfill_academic_records');

    expect(AcademicClass::pluck('sort_order', 'name')->all())->toBe(['Class One' => 1, 'Class Two' => 2, 'Class 12' => 12, 'Nursery' => null]);

    $session = AcademicSession::sole();
    expect($session->only(['name', 'is_current']))->toBe(['name' => '2026-2027', 'is_current' => true])
        ->and($session->starts_on->toDateString())->toBe('2026-04-01')
        ->and($session->ends_on->toDateString())->toBe('2027-03-31');

    expect($active->currentEnrollment->academic_class_id)->toBe($one->id)
        ->and($inactive->enrollments()->sole()->status)->toBe(EnrollmentStatus::Left)
        ->and($inactive->enrollments()->sole()->academic_class_id)->toBe($two->id)
        ->and($unplaced->enrollments()->exists())->toBeFalse();

    // Students and results are byte-for-byte unchanged
    expect(DB::table('students')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all())->toBe($before)
        ->and((array) DB::table('examination_results')->first())->toBe($resultBefore)
        ->and($result->fresh()->student_id)->toBe($active->id);

    // Running it again changes nothing
    runMigration('2026_10_25_100100_backfill_academic_records');
    expect(Enrollment::count())->toBe(2)->and(AcademicSession::count())->toBe(1);

    Carbon::setTestNow();
});

it('creates no session on a fresh install without students', function () {
    runMigration('2026_10_25_100100_backfill_academic_records');

    expect(AcademicSession::count())->toBe(0);
});

it('stops before adding the unique index when admission numbers are duplicated', function () {
    // Schema changes inside the test transaction are only safe on SQLite
    runMigration('2026_10_25_100000_create_academic_structure_tables', 'down');
    Student::factory()->count(2)->create(['admission_number' => 'SAME']);

    expect(fn () => runMigration('2026_10_25_100000_create_academic_structure_tables'))
        ->toThrow(RuntimeException::class, 'SAME');
})->skip(fn () => DB::getDriverName() !== 'sqlite', 'Needs transactional schema changes (SQLite)');
