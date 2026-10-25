<?php

namespace App\Filament\Pages\Concerns;

use App\Models\AcademicSession;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Attendance\AttendanceScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Shared by the attendance pages. The date and register key are plain
 * strings from the browser, so both are parsed and permission-checked again
 * on every request rather than trusted.
 */
trait ResolvesAttendanceGroup
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && app(AttendanceScope::class)->canUseAttendance($user);
    }

    protected function parseDate(?string $value, ?Carbon $fallback = null): Carbon
    {
        try {
            $date = Carbon::createFromFormat('!Y-m-d', (string) $value);

            if ($date && $date->format('Y-m-d') === $value) {
                return $date;
            }
        } catch (Throwable) {
            // fall through to the fallback
        }

        return $fallback ?? today();
    }

    protected function sessionCovering(Carbon $date): ?AcademicSession
    {
        return AcademicSession::query()
            ->whereDate('starts_on', '<=', $date->toDateString())
            ->whereDate('ends_on', '>=', $date->toDateString())
            ->first();
    }

    /**
     * @return Collection<int, AttendanceGroup>
     */
    protected function groupsIn(?AcademicSession $session): Collection
    {
        return $session ? app(AttendanceScope::class)->groupsFor(auth()->user(), $session) : collect();
    }

    // The register the key names, but only if this user may see it in this session
    protected function allowedGroup(?string $key, ?AcademicSession $session): ?AttendanceGroup
    {
        $groups = $this->groupsIn($session);

        return $groups->first(fn (AttendanceGroup $group) => $group->key() === $key) ?? $groups->first();
    }
}
