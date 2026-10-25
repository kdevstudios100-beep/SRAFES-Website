<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Models\AcademicClass;
use App\Models\ExamPaper;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Support\Exams\GradingScale;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The papers of an exam: one per class and subject, with maximum and pass marks.
 */
class PapersRelationManager extends RelationManager
{
    protected static string $relationship = 'papers';

    protected static ?string $title = 'Papers';

    protected static ?string $modelLabel = 'paper';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('academic_class_id')
                    ->label('Class')
                    ->options(fn () => AcademicClass::query()->ordered()->pluck('name', 'id'))
                    ->required()
                    ->live()
                    // Marks are entered against the class's students, so the class is fixed once marking starts
                    ->disabled(fn (?ExamPaper $record) => $record?->marks()->exists() ?? false),
                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn () => Subject::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabled(fn (?ExamPaper $record) => $record?->marks()->exists() ?? false)
                    ->rule(fn (Get $get, ?ExamPaper $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        $exists = $this->getOwnerRecord()->papers()
                            ->where('academic_class_id', $get('academic_class_id'))
                            ->where('subject_id', $value)
                            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                            ->exists();

                        if ($exists) {
                            $fail('This class already has a paper in this subject.');
                        }
                    }),
                TextInput::make('total_marks')
                    ->label('Maximum marks')
                    ->integer()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->default(100)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, $state) => filled($state) && ctype_digit((string) $state)
                        ? $set('pass_marks', app(GradingScale::class)->suggestedPassMarks((int) $state))
                        : null)
                    ->rule(fn (?ExamPaper $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                        $highest = $record?->marks()->max('obtained_marks');

                        if ($highest !== null && (int) $value < $highest) {
                            $fail("A student already has {$highest} marks on this paper.");
                        }
                    }),
                TextInput::make('pass_marks')
                    ->label('Pass marks')
                    ->integer()
                    ->minValue(0)
                    ->lte('total_marks')
                    ->default(fn () => app(GradingScale::class)->suggestedPassMarks(100))
                    ->required()
                    ->helperText('Suggested: '.config('srafes.exams.pass_percent', 33).'% of the maximum.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['academicClass', 'subject'])->withCount('marks'))
            ->defaultSort(fn (Builder $query) => $query
                ->orderBy(AcademicClass::query()->select('sort_order')->whereColumn('academic_classes.id', 'exam_papers.academic_class_id'))
                ->orderBy(Subject::query()->select('name')->whereColumn('subjects.id', 'exam_papers.subject_id')))
            ->columns([
                TextColumn::make('academicClass.name')
                    ->label('Class'),
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable(),
                TextColumn::make('total_marks')
                    ->label('Maximum'),
                TextColumn::make('pass_marks')
                    ->label('Pass'),
                TextColumn::make('marks_count')
                    ->label('Marks entered'),
            ])
            ->filters([
                SelectFilter::make('academic_class_id')
                    ->label('Class')
                    ->options(fn () => AcademicClass::query()->ordered()->pluck('name', 'id')),
            ])
            ->headerActions([
                $this->addClassSubjectsAction(),
                CreateAction::make()
                    ->visible(fn () => $this->canCreatePapers()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    // One paper for every subject the class takes, skipping subjects it already has a paper in
    private function addClassSubjectsAction(): Action
    {
        return Action::make('addClassSubjects')
            ->label('Add a class\'s subjects')
            ->icon('heroicon-m-squares-plus')
            ->color('gray')
            ->visible(fn () => $this->canCreatePapers())
            ->modalDescription('Adds a paper for each subject set up for the class under Academics → Classes → Subjects.')
            ->schema([
                Select::make('academic_class_id')
                    ->label('Class')
                    ->options(fn () => AcademicClass::query()->ordered()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('total_marks')
                    ->label('Maximum marks for each paper')
                    ->integer()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->default(100)
                    ->required(),
            ])
            ->action(function (array $data) {
                $exam = $this->getOwnerRecord();

                $subjectIds = SubjectAssignment::query()
                    ->where('academic_class_id', $data['academic_class_id'])
                    ->whereHas('subject', fn (Builder $query) => $query->where('is_active', true))
                    ->distinct()
                    ->pluck('subject_id')
                    ->diff($exam->papers()->where('academic_class_id', $data['academic_class_id'])->pluck('subject_id'));

                if ($subjectIds->isEmpty()) {
                    Notification::make()->warning()
                        ->title('No papers added')
                        ->body('This class has no subjects without a paper. Add its subjects under Academics → Classes first, or add papers one by one.')
                        ->send();

                    return;
                }

                $total = (int) $data['total_marks'];

                foreach ($subjectIds as $subjectId) {
                    $exam->papers()->create([
                        'academic_class_id' => $data['academic_class_id'],
                        'subject_id' => $subjectId,
                        'total_marks' => $total,
                        'pass_marks' => app(GradingScale::class)->suggestedPassMarks($total),
                    ]);
                }

                Notification::make()->success()->title("Added {$subjectIds->count()} ".str('paper')->plural($subjectIds->count()))->send();
            });
    }

    private function canCreatePapers(): bool
    {
        return auth()->user()->can('create', ExamPaper::class) && ! $this->getOwnerRecord()->isLocked();
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewAny', ExamPaper::class);
    }
}
