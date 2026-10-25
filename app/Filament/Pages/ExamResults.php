<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ResolvesExam;
use App\Models\Exam;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Exams\ExamScope;
use App\Support\Exams\ResultSheet;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * One class or section's result sheet for an exam: every paper, totals,
 * grades and positions, with a CSV download and printable result cards.
 * Teachers see only the classes they are class teacher of.
 */
class ExamResults extends Page
{
    use ResolvesExam;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Exam results';

    protected string $view = 'filament.pages.exam-results';

    #[Url]
    public ?string $exam = null;

    #[Url]
    public ?string $group = null;

    public function mount(): void
    {
        $this->updatedExam();
    }

    public function updatedExam(): void
    {
        unset($this->exams, $this->selectedExam, $this->groups, $this->register, $this->papers, $this->rows);
        $this->exam = $this->selectedExam ? (string) $this->selectedExam->id : null;
        $this->group = $this->register?->key();
    }

    public function updatedGroup(): void
    {
        unset($this->register, $this->papers, $this->rows);
        $this->group = $this->register?->key();
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
        return $this->selectedExam ? app(ExamScope::class)->resultGroupsFor(auth()->user(), $this->selectedExam) : collect();
    }

    #[Computed]
    public function register(): ?AttendanceGroup
    {
        return $this->resolveGroup($this->group, $this->groups);
    }

    #[Computed]
    public function papers(): Collection
    {
        return $this->register ? app(ExamScope::class)->papersIn($this->selectedExam, $this->register) : collect();
    }

    #[Computed]
    public function rows(): Collection
    {
        return $this->register ? app(ResultSheet::class)->forGroup($this->selectedExam, $this->register, $this->papers) : collect();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printSheet')
                ->label('Print result sheet')
                ->icon('heroicon-m-document-text')
                ->color('gray')
                ->visible(fn () => $this->rows->isNotEmpty())
                ->url(fn () => route('exams.result-sheet', ['exam' => $this->selectedExam, 'group' => $this->register->key()]), shouldOpenInNewTab: true),
            Action::make('print')
                ->label('Print result cards')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->visible(fn () => $this->rows->isNotEmpty())
                ->url(fn () => route('exams.result-cards', ['exam' => $this->selectedExam, 'group' => $this->register->key()]), shouldOpenInNewTab: true),
            Action::make('download')
                ->label('Download CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->rows->isNotEmpty())
                ->action(fn () => $this->csv()),
        ];
    }

    private function csv(): StreamedResponse
    {
        abort_unless(static::canAccess() && $this->register, 403);

        $rows = $this->rows;
        $papers = $this->papers;
        $exam = $this->selectedExam;
        $register = $this->register;

        return response()->streamDownload(function () use ($rows, $papers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'roll_number', 'admission_number', 'name',
                ...$papers->map(fn ($paper) => Str::slug($paper->subject->name, '_').'_of_'.$paper->total_marks),
                'obtained', 'total', 'percent', 'grade', 'result', 'position',
            ], ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['enrollment']->roll_number,
                    $row['enrollment']->student->admission_number,
                    $row['enrollment']->student->name,
                    ...$papers->map(fn ($paper) => match (true) {
                        $row['marks'][$paper->id] === null => '',
                        $row['marks'][$paper->id]->is_absent => 'absent',
                        default => $row['marks'][$paper->id]->obtained_marks,
                    }),
                    $row['obtained'],
                    $row['total'],
                    $row['percent'],
                    $row['grade'],
                    $row['result']?->value ?? 'incomplete',
                    $row['position'],
                ], ',', '"', '');
            }

            fclose($out);
        }, 'results-'.Str::slug($exam->academicSession->name.' '.$exam->name.' '.$register->label()).'.csv', ['Content-Type' => 'text/csv']);
    }
}
