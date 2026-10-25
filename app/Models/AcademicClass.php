<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('name');
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function examinationResults(): HasMany
    {
        return $this->hasMany(ExaminationResult::class);
    }

    // Ladder order; classes without a position go last, by name
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw('sort_order IS NULL')->orderBy('sort_order')->orderBy('name');
    }

    // The class students are promoted into, or null for the final class
    public function nextClass(): ?self
    {
        if ($this->sort_order === null) {
            return null;
        }

        return static::query()->where('sort_order', '>', $this->sort_order)->ordered()->first();
    }

    // Deleting a class that holds students, enrollments or results would erase or orphan history
    public function isInUse(): bool
    {
        return $this->students()->exists()
            || $this->enrollments()->exists()
            || $this->examinationResults()->exists();
    }
}
