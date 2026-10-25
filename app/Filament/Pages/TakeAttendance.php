<?php

namespace App\Filament\Pages;

use App\Enum\AttendanceStatus;
use App\Filament\Pages\Concerns\ResolvesAttendanceGroup;
use App\Support\Attendance\AttendanceException;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Attendance\AttendanceRegister;
use App\Support\Attendance\AttendanceScope;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Mark a day's attendance for one class or section. Used in the admin panel
 * (any register) and the teacher panel (only the teacher's own registers).
 */
class TakeAttendance extends Page
{
    use ResolvesAttendanceGroup;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Take attendance';

    protected string $view = 'filament.pages.take-attendance';

    #[Url]
    public ?string $date = null;

    #[Url]
    public ?string $group = null;

    /** @var array<int|string, array{status: ?string, remarks: ?string}> */
    public array $marks = [];

    public function mount(): void
    {
        $this->updatedDate();
    }

    public function updatedDate(): void
    {
        $this->date = $this->parseDate($this->date)->toDateString();
        $this->forgetComputed();
        // Keep the chosen register if it exists in the new date's session, otherwise fall back to the first allowed one
        $this->group = $this->register?->key();
        $this->loadMarks();
    }

    public function updatedGroup(): void
    {
        $this->loadMarks();
    }

    #[Computed]
    public function session()
    {
        return $this->sessionCovering($this->parseDate($this->date));
    }

    #[Computed]
    public function groups(): Collection
    {
        return $this->groupsIn($this->session);
    }

    #[Computed]
    public function register(): ?AttendanceGroup
    {
        return $this->allowedGroup($this->group, $this->session);
    }

    #[Computed]
    public function roster(): Collection
    {
        return $this->register ? app(AttendanceScope::class)->roster($this->register, $this->parseDate($this->date)) : collect();
    }

    #[Computed]
    public function existing(): Collection
    {
        return $this->register ? app(AttendanceRegister::class)->existing($this->register, $this->parseDate($this->date)) : collect();
    }

    #[Computed]
    public function lockedReason(): ?string
    {
        return $this->register ? app(AttendanceScope::class)->whyCannotMark(auth()->user(), $this->register, $this->parseDate($this->date)) : null;
    }

    public function markAll(string $status): void
    {
        if (! AttendanceStatus::tryFrom($status) || $this->lockedReason) {
            return;
        }

        foreach ($this->roster as $enrollment) {
            $this->marks[$enrollment->id]['status'] = $status;
        }
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        if (! $this->register) {
            return;
        }

        try {
            $saved = app(AttendanceRegister::class)->save(auth()->user(), $this->register, $this->parseDate($this->date), $this->marks);
        } catch (AttendanceException $exception) {
            Notification::make()->danger()->title('Attendance not saved')->body($exception->getMessage())->send();

            return;
        }

        unset($this->existing);

        Notification::make()->success()->title("Attendance saved for {$saved} students")->send();
    }

    public function statuses(): array
    {
        return AttendanceStatus::cases();
    }

    private function loadMarks(): void
    {
        $this->forgetComputed();

        $this->marks = $this->roster->mapWithKeys(fn ($enrollment) => [$enrollment->id => [
            'status' => $this->existing->get($enrollment->id)?->status?->value,
            'remarks' => $this->existing->get($enrollment->id)?->remarks,
        ]])->all();
    }

    private function forgetComputed(): void
    {
        unset($this->session, $this->groups, $this->register, $this->roster, $this->existing, $this->lockedReason);
    }
}
