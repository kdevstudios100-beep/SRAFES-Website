<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ResolvesAttendanceGroup;
use App\Support\Attendance\AttendanceSummary;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Which registers were taken on a day, with totals. Teachers see only their
 * own classes; this is the teacher panel's home page.
 */
class AttendanceOverview extends Page
{
    use ResolvesAttendanceGroup;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Daily overview';

    protected static ?string $title = 'Attendance overview';

    protected string $view = 'filament.pages.attendance-overview';

    #[Url]
    public ?string $date = null;

    public function mount(): void
    {
        $this->date = $this->parseDate($this->date)->toDateString();
    }

    public function updatedDate(): void
    {
        $this->date = $this->parseDate($this->date)->toDateString();
        unset($this->session, $this->rows);
    }

    #[Computed]
    public function session()
    {
        return $this->sessionCovering($this->parseDate($this->date));
    }

    #[Computed]
    public function rows(): Collection
    {
        return $this->session ? app(AttendanceSummary::class)->day(auth()->user(), $this->session, $this->parseDate($this->date)) : collect();
    }

    public function takeUrl(string $groupKey): string
    {
        return TakeAttendance::getUrl(['date' => $this->date, 'group' => $groupKey]);
    }
}
