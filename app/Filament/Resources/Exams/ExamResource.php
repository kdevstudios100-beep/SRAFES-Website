<?php

namespace App\Filament\Resources\Exams;

use App\Enum\ExamStatus;
use App\Filament\Pages\ExamResults;
use App\Filament\Resources\Exams\Pages\CreateExam;
use App\Filament\Resources\Exams\Pages\EditExam;
use App\Filament\Resources\Exams\Pages\ListExams;
use App\Filament\Resources\Exams\RelationManagers\PapersRelationManager;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Support\Exams\ExamException;
use App\Support\Exams\ResultSheet;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('academic_session_id')
                    ->label('Session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->default(fn () => AcademicSession::current()?->id)
                    ->required()
                    ->live()
                    // Marks belong to the session's enrollments, so the session is fixed once marking starts
                    ->disabled(fn (?Exam $record) => $record?->hasMarks() ?? false)
                    ->helperText(fn (?Exam $record) => $record?->hasMarks() ? 'Fixed: marks have been entered.' : null),
                TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Mid-term')
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('academic_session_id', $get('academic_session_id'))),
                DatePicker::make('held_on')
                    ->label('Held on')
                    ->helperText('Optional; shown on result cards.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('academicSession'))
            ->defaultSort(fn (Builder $query) => $query->latestFirst())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('academicSession.name')
                    ->label('Session'),
                TextColumn::make('held_on')
                    ->date()
                    ->placeholder('-'),
                TextColumn::make('papers_count')
                    ->label('Papers')
                    ->counts('papers'),
                TextColumn::make('status')
                    ->state(fn (Exam $record) => $record->status())
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('academic_session_id')
                    ->label('Session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->default(fn () => AcademicSession::current()?->id),
            ])
            ->recordActions([
                Action::make('results')
                    ->label('Results')
                    ->icon('heroicon-m-table-cells')
                    ->color('gray')
                    ->url(fn (Exam $record) => ExamResults::getUrl(['exam' => $record->id])),
                ActionGroup::make([
                    ...static::lifecycleActions(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    /**
     * Lock, unlock, publish and unpublish; shared by the table and the edit page.
     *
     * @return array<Action>
     */
    public static function lifecycleActions(): array
    {
        $run = function (callable $callback, string $success) {
            try {
                $callback();
            } catch (ExamException $exception) {
                Notification::make()->danger()->title('Not done')->body($exception->getMessage())->send();

                return;
            }

            Notification::make()->success()->title($success)->send();
        };

        $canManage = fn (Exam $record) => auth()->user()->can('update', $record);

        return [
            Action::make('lock')
                ->icon('heroicon-m-lock-closed')
                ->requiresConfirmation()
                ->modalDescription('Teachers will no longer be able to enter or change marks for this exam. You can unlock it again until results are published.')
                ->visible(fn (Exam $record) => $canManage($record) && $record->status() === ExamStatus::Open)
                ->action(fn (Exam $record) => $run(fn () => app(ResultSheet::class)->lock($record), "{$record->name} locked")),
            Action::make('unlock')
                ->icon('heroicon-m-lock-open')
                ->requiresConfirmation()
                ->modalDescription('Marks can be entered and changed again.')
                ->visible(fn (Exam $record) => $canManage($record) && $record->status() === ExamStatus::Locked)
                ->action(fn (Exam $record) => $run(fn () => app(ResultSheet::class)->unlock($record), "{$record->name} unlocked")),
            Action::make('publish')
                ->label('Publish results')
                ->icon('heroicon-m-globe-alt')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Each student\'s total, grade and result will appear on the public Examination Results page, found by admission number. Every student must have every mark entered.')
                ->visible(fn (Exam $record) => $canManage($record) && $record->status() === ExamStatus::Locked)
                ->action(fn (Exam $record) => $run(fn () => app(ResultSheet::class)->publish($record), "Results published for {$record->name}")),
            Action::make('unpublish')
                ->label('Unpublish results')
                ->icon('heroicon-m-eye-slash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('This exam\'s results are removed from the public Examination Results page. The marks are kept.')
                ->visible(fn (Exam $record) => $canManage($record) && $record->status() === ExamStatus::Published)
                ->action(fn (Exam $record) => $run(fn () => app(ResultSheet::class)->unpublish($record), "Results unpublished for {$record->name}")),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PapersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExams::route('/'),
            'create' => CreateExam::route('/create'),
            'edit' => EditExam::route('/{record}/edit'),
        ];
    }
}
