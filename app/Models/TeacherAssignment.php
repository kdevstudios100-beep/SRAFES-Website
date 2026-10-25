<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A teacher's role in one session: class teacher of a class or section
 * (no subject; takes daily attendance) or teacher of one subject there.
 */
class TeacherAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'academic_session_id',
        'academic_class_id',
        'section_id',
        'subject_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeClassTeacher(Builder $query): Builder
    {
        return $query->whereNull('subject_id');
    }

    public function isClassTeacher(): bool
    {
        return $this->subject_id === null;
    }

    // "Class 5 - A"
    public function placement(): Attribute
    {
        return Attribute::get(fn () => $this->academicClass?->name.($this->section ? ' - '.$this->section->name : ''));
    }
}
