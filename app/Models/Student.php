<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'academic_class_id',
        'admission_number',
        'first_name',
        'last_name',
        'surname',
        'father_name',
        'date_of_birth',
        'is_active',
        'has_scholarship',
    ];

    protected static function booted(): void
    {
        // Delete results through Eloquent so their uploaded result files are removed too
        static::deleting(function (Student $student) {
            $student->examinationResults->each->delete();
        });
    }

    public function getCasts(): array
    {
        return [
            'is_active' => 'boolean',
            'has_scholarship' => 'boolean',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function examinationResults(): HasMany
    {
        return $this->hasMany(ExaminationResult::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useFallbackUrl('https://placehold.co/640x400')
            ->singleFile();
    }

    public function scopeHasScholarship(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('has_scholarship', true);
    }

    public function name(): Attribute
    {
        return Attribute::get(fn () => $this->first_name.' '.$this->last_name);
    }
}
