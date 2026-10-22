<?php

namespace App\Models;

use App\Enum\EmployeeRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function getCasts(): array
    {
        return [
            'role' => EmployeeRole::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query
            ->where('is_featured', true)
            ->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTeacher(Builder $query): Builder
    {
        return $query->where('role', EmployeeRole::Teacher);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useFallbackUrl('https://placehold.co/640x400')
            ->singleFile();
    }
}
