<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'salary_range',
        'application_deadline',
        'is_active',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'is_active' => 'boolean',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('application_deadline')
                ->orWhereDate('application_deadline', '>=', now()->toDateString()));
    }

    public function isOpen(): bool
    {
        return $this->is_active
            && ($this->application_deadline === null || ! $this->application_deadline->endOfDay()->isPast());
    }
}
