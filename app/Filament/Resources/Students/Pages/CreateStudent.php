<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\Actions\PlacementActions;
use App\Filament\Resources\Students\StudentResource;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Section;
use App\Models\Student;
use App\Support\Academics\Enrollments;
use App\Support\Academics\RollNumberTakenException;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    private const ENROLLMENT_FIELDS = ['academic_class_id', 'section_id', 'roll_number'];

    protected function handleRecordCreation(array $data): Model
    {
        $placement = Arr::only($data, self::ENROLLMENT_FIELDS);
        $session = AcademicSession::current();

        if (filled($placement['academic_class_id'] ?? null)) {
            if (! PlacementActions::allowed()) {
                throw ValidationException::withMessages(['data.academic_class_id' => 'You are not allowed to enroll students.']);
            }

            if (! $session) {
                throw ValidationException::withMessages(['data.academic_class_id' => 'Create an academic session (and make it current) before enrolling students.']);
            }
        }

        // Student and first placement are saved together, or not at all
        return DB::transaction(function () use ($data, $placement, $session) {
            $student = Student::create(Arr::except($data, self::ENROLLMENT_FIELDS) + ['is_active' => true]);

            if (filled($placement['academic_class_id'] ?? null)) {
                try {
                    app(Enrollments::class)->enroll(
                        $student,
                        $session,
                        AcademicClass::findOrFail($placement['academic_class_id']),
                        filled($placement['section_id'] ?? null) ? Section::findOrFail($placement['section_id']) : null,
                        $placement['roll_number'] ?? null,
                    );
                } catch (RollNumberTakenException $exception) {
                    // Rolls back the new student too, and shows the reason under the roll number field
                    throw ValidationException::withMessages(['data.roll_number' => $exception->getMessage()]);
                }
            }

            return $student;
        });
    }

    protected function getRedirectUrl(): string
    {
        return StudentResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
