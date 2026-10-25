<?php

namespace App\Support\Academics;

use App\Enum\EnrollmentStatus;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The only place student placements change. Every change closes the current
 * enrollment (keeping it as history) and, where the student stays in school,
 * opens a new one, all in one transaction. students.academic_class_id is kept
 * in step as the student's current (or last) class for existing pages.
 * Examination results are never touched.
 */
class Enrollments
{
    public function enroll(Student $student, AcademicSession $session, AcademicClass $class, ?Section $section = null, ?string $rollNumber = null, ?Carbon $on = null): Enrollment
    {
        return DB::transaction(function () use ($student, $session, $class, $section, $rollNumber, $on) {
            $this->lock($student);

            if ($student->enrollments()->active()->exists()) {
                throw new EnrollmentException("{$student->name} is already enrolled. Use transfer or promote instead.");
            }

            return $this->open($student, $session, $class, $section, $rollNumber, $on);
        });
    }

    // Move to another class or section within the same session
    public function transfer(Student $student, AcademicClass $class, ?Section $section = null, ?string $rollNumber = null, ?Carbon $on = null, ?string $notes = null): Enrollment
    {
        return DB::transaction(function () use ($student, $class, $section, $rollNumber, $on, $notes) {
            $current = $this->activeEnrollment($student);

            if ($current->academic_class_id === $class->id && $current->section_id === $section?->id) {
                throw new EnrollmentException("{$student->name} is already in {$current->placement}.");
            }

            $this->close($current, EnrollmentStatus::Transferred, $on, $notes);

            return $this->open($student, $current->academicSession, $class, $section, $rollNumber ?? $current->roll_number, $on);
        });
    }

    // Move into a later session: a higher class (promoted) or the same class again (retained)
    public function promote(Student $student, AcademicSession $toSession, AcademicClass $class, ?Section $section = null, ?Carbon $on = null): Enrollment
    {
        return DB::transaction(function () use ($student, $toSession, $class, $section, $on) {
            $current = $this->activeEnrollment($student);

            if (! $toSession->starts_on->greaterThan($current->academicSession->starts_on)) {
                throw new EnrollmentException("{$student->name} can only be promoted into a session after {$current->academicSession->name}.");
            }

            $status = $class->is($current->academicClass) ? EnrollmentStatus::Retained : EnrollmentStatus::Promoted;

            $this->close($current, $status, $on ?? $current->academicSession->ends_on);

            return $this->open($student, $toSession, $class, $section, null, $on ?? $toSession->starts_on);
        });
    }

    public function graduate(Student $student, ?Carbon $on = null, ?string $notes = null): Enrollment
    {
        return $this->leave($student, EnrollmentStatus::Graduated, $on, $notes);
    }

    public function withdraw(Student $student, ?Carbon $on = null, ?string $notes = null): Enrollment
    {
        return $this->leave($student, EnrollmentStatus::Left, $on, $notes);
    }

    /**
     * Promote many students at once. Each student is handled in its own
     * transaction, so one refusal never undoes the others.
     *
     * @param  'next'|'same'|'specific'  $mode  next class on the ladder, the same class again, or $class
     * @return array{moved: int, skipped: array<string, string>} skipped is student name => reason
     */
    public function promoteMany(Collection $students, AcademicSession $toSession, string $mode, ?AcademicClass $class = null, ?Section $section = null): array
    {
        $moved = 0;
        $skipped = [];

        foreach ($students as $student) {
            try {
                $current = $student->enrollments()->active()->with('academicClass', 'section')->first();

                if (! $current) {
                    throw new EnrollmentException('not currently enrolled');
                }

                [$targetClass, $targetSection] = match ($mode) {
                    'same' => [$current->academicClass, $current->section],
                    'next' => $this->nextPlacement($current),
                    'specific' => [$class, $section],
                };

                $this->promote($student, $toSession, $targetClass, $targetSection);
                $moved++;
            } catch (EnrollmentException $exception) {
                $skipped[$student->name.' ('.($student->admission_number ?? "#{$student->id}").')'] = $exception->getMessage();
            }
        }

        return ['moved' => $moved, 'skipped' => $skipped];
    }

    /**
     * @return array{0: AcademicClass, 1: ?Section}
     */
    private function nextPlacement(Enrollment $current): array
    {
        $next = $current->academicClass->nextClass();

        if (! $next) {
            throw new EnrollmentException("{$current->academicClass->name} is the final class (or has no position set); graduate the student instead");
        }

        // Keep the same section letter when the next class has one
        $section = $current->section ? $next->sections()->where('name', $current->section->name)->first() : null;

        return [$next, $section];
    }

    private function leave(Student $student, EnrollmentStatus $status, ?Carbon $on, ?string $notes): Enrollment
    {
        return DB::transaction(function () use ($student, $status, $on, $notes) {
            $current = $this->activeEnrollment($student);

            $this->close($current, $status, $on, $notes);
            $student->update(['is_active' => false]);

            return $current;
        });
    }

    /**
     * The active placement already holding $rollNumber in this session, class
     * and section, if any. Roll numbers repeat across classes and sections,
     * never within one.
     */
    public function rollNumberHolder(AcademicSession $session, AcademicClass $class, ?Section $section, ?string $rollNumber, ?Enrollment $except = null): ?Enrollment
    {
        if (blank($rollNumber)) {
            return null;
        }

        return Enrollment::query()
            ->active()
            ->where('academic_session_id', $session->id)
            ->where('academic_class_id', $class->id)
            ->where(fn (Builder $query) => $section ? $query->where('section_id', $section->id) : $query->whereNull('section_id'))
            ->where('roll_number', trim($rollNumber))
            ->when($except, fn (Builder $query) => $query->whereKeyNot($except->getKey()))
            ->with('student')
            ->first();
    }

    private function open(Student $student, AcademicSession $session, AcademicClass $class, ?Section $section, ?string $rollNumber, ?Carbon $on): Enrollment
    {
        if ($section && $section->academic_class_id !== $class->id) {
            throw new EnrollmentException("Section {$section->name} does not belong to {$class->name}.");
        }

        if ($holder = $this->rollNumberHolder($session, $class, $section, $rollNumber)) {
            throw new RollNumberTakenException("Roll number {$holder->roll_number} in {$holder->placement} already belongs to {$holder->student->name}. Choose another roll number.");
        }

        $enrollment = $student->enrollments()->create([
            'academic_session_id' => $session->id,
            'academic_class_id' => $class->id,
            'section_id' => $section?->id,
            'roll_number' => filled($rollNumber) ? trim($rollNumber) : null,
            'status' => EnrollmentStatus::Active,
            'enrolled_on' => ($on ?? Carbon::today())->toDateString(),
        ]);

        $student->update(['academic_class_id' => $class->id, 'is_active' => true]);

        return $enrollment;
    }

    private function close(Enrollment $enrollment, EnrollmentStatus $status, ?Carbon $on, ?string $notes = null): void
    {
        $enrollment->update([
            'status' => $status,
            'ended_on' => ($on ?? Carbon::today())->toDateString(),
            'notes' => filled($notes) ? trim($enrollment->notes."\n".$notes) : $enrollment->notes,
        ]);
    }

    private function activeEnrollment(Student $student): Enrollment
    {
        $this->lock($student);

        return $student->enrollments()->active()->with('academicSession', 'academicClass', 'section')->first()
            ?? throw new EnrollmentException("{$student->name} is not currently enrolled.");
    }

    // Serialises concurrent changes to the same student (a no-op on SQLite, which locks the whole database)
    private function lock(Student $student): void
    {
        Student::query()->whereKey($student->getKey())->lockForUpdate()->first();
    }
}
