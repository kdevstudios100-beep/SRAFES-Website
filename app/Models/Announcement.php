<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Announcement extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function getCasts()
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    public function scopeToday(Builder $query): Builder
    {
        $today = now()->toDateString();
        $todayMonthDay = now()->format('m-d');

        $startMonthDay = static::monthDayExpression($query, 'start_date');
        // A recurring announcement without an end date runs for its start day only
        $endMonthDay = 'COALESCE('.static::monthDayExpression($query, 'end_date').", {$startMonthDay})";

        // Wrap both branches so the OR can't leak into other where clauses on the query
        return $query->where(fn (Builder $query) => $query
            ->where(
                fn (Builder $query) => $query
                    ->where('is_recurring', true)
                    ->whereDate('start_date', '<=', $today)
                    // Compare month-day only; a range like 25 Dec - 5 Jan wraps over the new year
                    ->whereRaw(
                        "(({$startMonthDay} <= {$endMonthDay} AND ? BETWEEN {$startMonthDay} AND {$endMonthDay})"
                        ." OR ({$startMonthDay} > {$endMonthDay} AND (? >= {$startMonthDay} OR ? <= {$endMonthDay})))",
                        [$todayMonthDay, $todayMonthDay, $todayMonthDay]
                    )
            )
            ->orWhere(
                fn (Builder $query) => $query
                    ->where('is_recurring', false)
                    ->whereDate('start_date', '<=', $today)
                    ->where(function (Builder $query) use ($today) {
                        $query
                            ->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $today);
                    })
            ));
    }

    protected static function monthDayExpression(Builder $query, string $column): string
    {
        $column = $query->getQuery()->getGrammar()->wrap($column);

        return match ($query->getConnection()->getDriverName()) {
            'sqlite' => "strftime('%m-%d', {$column})",
            'pgsql' => "to_char({$column}, 'MM-DD')",
            'sqlsrv' => "FORMAT({$column}, 'MM-dd')",
            default => "DATE_FORMAT({$column}, '%m-%d')",
        };
    }
}
