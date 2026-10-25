<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A subject taught to a whole class (no section) or to one section of it.
 */
class SubjectAssignment extends Model
{
    protected $fillable = [
        'academic_class_id',
        'section_id',
        'subject_id',
    ];

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

    // Subjects for a placement: the class's whole-class subjects plus the section's own
    public function scopeForPlacement(Builder $query, int $academicClassId, ?int $sectionId): Builder
    {
        return $query
            ->where('academic_class_id', $academicClassId)
            ->where(fn (Builder $query) => $query
                ->whereNull('section_id')
                ->when($sectionId, fn (Builder $query) => $query->orWhere('section_id', $sectionId)));
    }
}
