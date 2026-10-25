<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_on',
        'ends_on',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public static function current(): ?self
    {
        return static::query()->where('is_current', true)->first();
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('starts_on');
    }

    // Switch in one transaction so there is never zero or two current sessions
    public function makeCurrent(): void
    {
        DB::transaction(function () {
            static::query()->whereKeyNot($this->getKey())->update(['is_current' => false]);
            $this->update(['is_current' => true]);
        });
    }

    /**
     * Suggested name and dates for the school year containing $date, based on
     * config('srafes.academics.session_start_month').
     *
     * @return array{name: string, starts_on: Carbon, ends_on: Carbon}
     */
    public static function suggestedFor(Carbon $date): array
    {
        $startMonth = (int) config('srafes.academics.session_start_month', 4);
        $startYear = $date->month >= $startMonth ? $date->year : $date->year - 1;
        $startsOn = Carbon::create($startYear, $startMonth, 1)->startOfDay();
        $endsOn = $startsOn->copy()->addYear()->subDay();

        return [
            'name' => $startsOn->year === $endsOn->year ? (string) $startsOn->year : "{$startsOn->year}-{$endsOn->year}",
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
        ];
    }

    // The year after the newest session, or the current school year when there are none
    public static function suggestedNext(): array
    {
        $latest = static::query()->latestFirst()->first();

        return static::suggestedFor($latest ? $latest->ends_on->copy()->addDay() : Carbon::today());
    }
}
