<?php

namespace App\Models;

use App\Enum\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A student's placement in one session, class and section. Create and close
 * enrollments only through App\Support\Academics\Enrollments, so a student
 * never has two active placements and history is never overwritten.
 */
class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'academic_session_id',
        'academic_class_id',
        'section_id',
        'roll_number',
        'status',
        'enrolled_on',
        'ended_on',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrolled_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // Placements in force on $date. The end date is exclusive: on a transfer day the student
    // belongs to the new placement only, so they never appear on two registers.
    public function scopeCoveringDate(Builder $query, string $date): Builder
    {
        return $query
            ->where(fn (Builder $query) => $query->whereNull('enrolled_on')->orWhereDate('enrolled_on', '<=', $date))
            ->where(fn (Builder $query) => $query->where('status', EnrollmentStatus::Active)->orWhereDate('ended_on', '>', $date));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Active);
    }

    // "Class 5 - A", or just "Class 5" without a section
    public function placement(): Attribute
    {
        return Attribute::get(fn () => $this->academicClass?->name.($this->section ? ' - '.$this->section->name : ''));
    }
}
