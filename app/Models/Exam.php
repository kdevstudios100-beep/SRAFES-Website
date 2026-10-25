<?php

namespace App\Models;

use App\Enum\ExamStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * One examination in a session, e.g. "Mid-term". Lock, unlock, publish and
 * unpublish only through App\Support\Exams\ResultSheet, which keeps the
 * public results in step.
 */
class Exam extends Model
{
    protected $fillable = [
        'academic_session_id',
        'name',
        'held_on',
    ];

    protected function casts(): array
    {
        return [
            'held_on' => 'date',
            'locked_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(ExamPaper::class);
    }

    public function marks(): HasManyThrough
    {
        return $this->hasManyThrough(ExamMark::class, ExamPaper::class);
    }

    public function examinationResults(): HasMany
    {
        return $this->hasMany(ExaminationResult::class);
    }

    public function status(): ExamStatus
    {
        return match (true) {
            $this->published_at !== null => ExamStatus::Published,
            $this->locked_at !== null => ExamStatus::Locked,
            default => ExamStatus::Open,
        };
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    // Deleting an exam that holds marks would erase them
    public function hasMarks(): bool
    {
        return $this->marks()->exists();
    }

    // Newest session first, then by date held and name
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc(AcademicSession::query()->select('starts_on')->whereColumn('academic_sessions.id', 'exams.academic_session_id'))
            ->orderByRaw('held_on IS NULL')
            ->orderByDesc('held_on')
            ->orderBy('name');
    }

    // "Mid-term (2026-2027)"
    public function fullName(): string
    {
        return $this->name.' ('.$this->academicSession->name.')';
    }
}
