<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ResolvesExam;
use App\Models\Exam;
use App\Models\ExamPaper;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Exams\ExamException;
use App\Support\Exams\ExamScope;
use App\Support\Exams\MarkSheet;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Enter one paper's marks for one class or section. Used in the admin panel
 * (any paper) and the teacher panel (only papers the teacher may enter).
 */
class EnterMarks extends Page
{
    use ResolvesExam;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Enter marks';

    protected string $view = 'filament.pages.enter-marks';

    #[Url]
    public ?string $exam = null;

    #[Url]
    public ?string $group = null;

    #[Url]
    public ?string $paper = null;

    /** @var array<int|string, array{obtained: ?string, absent: bool, remarks: ?string}> */
    public array $marks = [];

    public function mount(): void
    {
        $this->updatedExam();
    }

    public function updatedExam(): void
    {
        $this->forgetComputed();
        $this->exam = $this->selectedExam ? (string) $this->selectedExam->id : null;
        // Keep the chosen class and subject where the new exam has them, otherwise fall back to the first allowed
        $this->group = $this->register?->key();
        $this->paper = $this->selectedPaper ? (string) $this->selectedPaper->id : null;
        $this->loadMarks();
    }

    public function updatedGroup(): void
    {
        $this->forgetComputed();
        $this->group = $this->register?->key();
        $this->paper = $this->selectedPaper ? (string) $this->selectedPaper->id : null;
        $this->loadMarks();
    }

    public function updatedPaper(): void
    {
        $this->forgetComputed();
        $this->paper = $this->selectedPaper ? (string) $this->selectedPaper->id : null;
        $this->loadMarks();
    }

    #[Computed]
    public function exams(): Collection
    {
        return $this->examOptions();
    }

    #[Computed]
    public function selectedExam(): ?Exam
    {
        return $this->resolveExam($this->exam, $this->exams);
    }

    #[Computed]
    public function groups(): Collection
    {
        return $this->selectedExam ? app(ExamScope::class)->groupsFor(auth()->user(), $this->selectedExam) : collect();
    }

    #[Computed]
    public function register(): ?AttendanceGroup
    {
        return $this->resolveGroup($this->group, $this->groups);
    }

    #[Computed]
    public function papers(): Collection
    {
        return $this->register ? app(ExamScope::class)->papersFor(auth()->user(), $this->selectedExam, $this->register) : collect();
    }

    #[Computed]
    public function selectedPaper(): ?ExamPaper
    {
        return $this->papers->firstWhere('id', (int) $this->paper) ?? $this->papers->first();
    }

    #[Computed]
    public function roster(): Collection
    {
        return $this->selectedPaper ? app(ExamScope::class)->roster($this->selectedExam, $this->register) : collect();
    }

    #[Computed]
    public function existing(): Collection
    {
        return $this->selectedPaper ? app(MarkSheet::class)->existing($this->selectedPaper, $this->register) : collect();
    }

    #[Computed]
    public function lockedReason(): ?string
    {
        return $this->selectedPaper ? app(ExamScope::class)->whyCannotEnter(auth()->user(), $this->selectedExam, $this->register, $this->selectedPaper) : null;
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        if (! $this->selectedPaper) {
            return;
        }

        try {
            $saved = app(MarkSheet::class)->save(auth()->user(), $this->selectedExam, $this->register, $this->selectedPaper, $this->marks);
        } catch (ExamException $exception) {
            Notification::make()->danger()->title('Marks not saved')->body($exception->getMessage())->send();

            return;
        }

        $this->loadMarks();

        $missing = $this->roster->count() - $saved;

        Notification::make()->success()
            ->title("Marks saved for {$saved} ".str('student')->plural($saved))
            ->body($missing > 0 ? "{$missing} still to enter." : null)
            ->send();
    }

    private function loadMarks(): void
    {
        unset($this->roster, $this->existing, $this->lockedReason);

        $this->marks = $this->roster->mapWithKeys(fn ($enrollment) => [$enrollment->id => [
            'obtained' => $this->existing->get($enrollment->id)?->obtained_marks !== null ? (string) $this->existing->get($enrollment->id)->obtained_marks : null,
            'absent' => (bool) $this->existing->get($enrollment->id)?->is_absent,
            'remarks' => $this->existing->get($enrollment->id)?->remarks,
        ]])->all();
    }

    private function forgetComputed(): void
    {
        unset($this->exams, $this->selectedExam, $this->groups, $this->register, $this->papers, $this->selectedPaper, $this->roster, $this->existing, $this->lockedReason);
    }
}
