<?php

namespace App\Filament\Pages;

use App\Enum\AttendanceStatus;
use App\Filament\Pages\Concerns\ResolvesAttendanceGroup;
use App\Models\AcademicSession;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Attendance\AttendanceSummary;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Attendance totals and percentages per student for one register over a
 * date range, with a CSV download.
 */
class AttendanceReport extends Page
{
    use ResolvesAttendanceGroup;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Attendance report';

    protected string $view = 'filament.pages.attendance-report';

    #[Url]
    public ?string $session = null;

    #[Url]
    public ?string $group = null;

    #[Url]
    public ?string $from = null;

    #[Url]
    public ?string $to = null;

    public function mount(): void
    {
        $this->session ??= (string) AcademicSession::current()?->id;
        $this->updatedSession();
    }

    public function updatedSession(): void
    {
        unset($this->academicSession, $this->groups, $this->register, $this->rows);

        if ($academicSession = $this->academicSession) {
            // Keep the range inside the chosen session
            $this->from = $this->parseDate($this->from, $academicSession->starts_on)->max($academicSession->starts_on)->min($academicSession->ends_on)->toDateString();
            $this->to = $this->parseDate($this->to, today())->min($academicSession->ends_on)->max($academicSession->starts_on)->toDateString();
        }

        $this->group = $this->register?->key();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['group', 'from', 'to'], true)) {
            unset($this->register, $this->rows);
        }
    }

    #[Computed]
    public function sessions(): Collection
    {
        return AcademicSession::query()->latestFirst()->get();
    }

    #[Computed]
    public function academicSession(): ?AcademicSession
    {
        return $this->sessions->firstWhere('id', (int) $this->session);
    }

    #[Computed]
    public function groups(): Collection
    {
        return $this->groupsIn($this->academicSession);
    }

    #[Computed]
    public function register(): ?AttendanceGroup
    {
        return $this->allowedGroup($this->group, $this->academicSession);
    }

    #[Computed]
    public function rows(): Collection
    {
        if (! $this->register) {
            return collect();
        }

        [$from, $to] = $this->range();

        return app(AttendanceSummary::class)->forGroup($this->register, $from, $to);
    }

    public function lowThreshold(): int
    {
        return (int) config('srafes.attendance.low_attendance_percent', 75);
    }

    public function statuses(): array
    {
        return AttendanceStatus::cases();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->rows->isNotEmpty())
                ->action(fn () => $this->csv()),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(): array
    {
        $from = $this->parseDate($this->from, $this->academicSession->starts_on);
        $to = $this->parseDate($this->to, today());

        return $from->lte($to) ? [$from, $to] : [$to, $from];
    }

    private function csv(): StreamedResponse
    {
        abort_unless(static::canAccess() && $this->register, 403);

        [$from, $to] = $this->range();
        $rows = $this->rows;
        $register = $this->register;

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['roll_number', 'admission_number', 'name', 'present', 'absent', 'late', 'leave', 'days_marked', 'attendance_percent'], ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['enrollment']->roll_number,
                    $row['enrollment']->student->admission_number,
                    $row['enrollment']->student->name,
                    ...array_values($row['counts']),
                    $row['days'],
                    $row['percent'],
                ], ',', '"', '');
            }

            fclose($out);
        }, 'attendance-'.Str::slug($register->session->name.' '.$register->label()).'-'.$from->toDateString().'-to-'.$to->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }
}
