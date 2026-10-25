<?php

namespace App\Filament\Resources\Students\Actions;

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Support\Academics\EnrollmentException;
use App\Support\Academics\Enrollments;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * Admin actions that change where a student is placed. Each one goes through
 * App\Support\Academics\Enrollments, which keeps the previous placement as
 * history, and is only offered to users allowed to change placements.
 */
class PlacementActions
{
    public static function group(): ActionGroup
    {
        return ActionGroup::make([
            static::enroll(),
            static::transfer(),
            static::promote(),
            static::graduate(),
            static::withdraw(),
        ])
            ->label('Placement')
            ->icon('heroicon-m-arrows-right-left')
            ->button()
            ->visible(fn () => static::allowed());
    }

    public static function enroll(): Action
    {
        return Action::make('enroll')
            ->label(fn (Student $record) => $record->enrollments()->exists() ? 'Re-enroll' : 'Enroll')
            ->icon('heroicon-m-user-plus')
            ->visible(fn (Student $record) => static::allowed() && ! $record->currentEnrollment)
            ->modalDescription('Place the student in a class for a session. The student becomes active.')
            ->schema([
                static::sessionField(),
                ...static::classAndSectionFields(),
                TextInput::make('roll_number')->maxLength(50),
                DatePicker::make('enrolled_on')->default(today())->required(),
            ])
            ->action(fn (Student $record, array $data, Action $action) => static::run($action, 'Student enrolled', fn (Enrollments $enrollments) => $enrollments->enroll(
                $record,
                AcademicSession::findOrFail($data['academic_session_id']),
                AcademicClass::findOrFail($data['academic_class_id']),
                static::section($data),
                $data['roll_number'] ?? null,
                Carbon::parse($data['enrolled_on']),
            )));
    }

    public static function transfer(): Action
    {
        return Action::make('transfer')
            ->label('Transfer class / section')
            ->icon('heroicon-m-arrows-right-left')
            ->visible(fn (Student $record) => static::allowed() && $record->currentEnrollment)
            ->modalDescription(fn (Student $record) => "Move the student within the {$record->currentEnrollment?->academicSession?->name} session. Their placement in {$record->currentEnrollment?->placement} is kept as history.")
            ->fillForm(fn (Student $record) => [
                'academic_class_id' => $record->currentEnrollment?->academic_class_id,
                'section_id' => $record->currentEnrollment?->section_id,
                'roll_number' => $record->currentEnrollment?->roll_number,
                'transferred_on' => today()->toDateString(),
            ])
            ->schema([
                ...static::classAndSectionFields(),
                TextInput::make('roll_number')->maxLength(50),
                DatePicker::make('transferred_on')->required(),
                Textarea::make('notes')->label('Reason (optional)')->rows(2)->maxLength(1000),
            ])
            ->action(fn (Student $record, array $data, Action $action) => static::run($action, 'Student transferred', fn (Enrollments $enrollments) => $enrollments->transfer(
                $record,
                AcademicClass::findOrFail($data['academic_class_id']),
                static::section($data),
                $data['roll_number'] ?? null,
                Carbon::parse($data['transferred_on']),
                $data['notes'] ?? null,
            )));
    }

    public static function promote(): Action
    {
        return Action::make('promote')
            ->label('Promote to next session')
            ->icon('heroicon-m-arrow-trending-up')
            ->visible(fn (Student $record) => static::allowed() && $record->currentEnrollment)
            ->fillForm(function (Student $record) {
                $current = $record->currentEnrollment;
                $next = $current?->academicClass?->nextClass();

                return [
                    'academic_session_id' => static::laterSessions($current)->keys()->first(),
                    'academic_class_id' => $next?->id ?? $current?->academic_class_id,
                    'section_id' => $current?->section ? $next?->sections()->where('name', $current->section->name)->value('id') : null,
                ];
            })
            ->modalDescription('Choose the same class to record that the student is repeating it.')
            ->schema(fn (Student $record) => [
                Select::make('academic_session_id')
                    ->label('Into session')
                    ->options(static::laterSessions($record->currentEnrollment))
                    ->required()
                    ->helperText(static::laterSessions($record->currentEnrollment)->isEmpty() ? 'Create the next academic session first.' : null),
                ...static::classAndSectionFields(),
            ])
            ->action(fn (Student $record, array $data, Action $action) => static::run($action, 'Student promoted', fn (Enrollments $enrollments) => $enrollments->promote(
                $record,
                AcademicSession::findOrFail($data['academic_session_id']),
                AcademicClass::findOrFail($data['academic_class_id']),
                static::section($data),
            )));
    }

    public static function graduate(): Action
    {
        return static::leaveAction('graduate', 'Mark as graduated', 'heroicon-m-academic-cap', 'Student marked as graduated', 'The student completed their final class. They become inactive and their placement is kept as history.');
    }

    public static function withdraw(): Action
    {
        return static::leaveAction('withdraw', 'Mark as left school', 'heroicon-m-arrow-left-start-on-rectangle', 'Student marked as left', 'The student left before completing school. They become inactive and their placement is kept as history.')
            ->color('danger');
    }

    public static function bulkPromote(): BulkAction
    {
        return BulkAction::make('promote')
            ->label('Promote / move to session')
            ->icon('heroicon-m-arrow-trending-up')
            ->visible(fn () => static::allowed())
            ->modalDescription(new HtmlString('Each selected student moves from their current placement into the chosen session. Their previous placement is kept as history. Students who cannot be moved are listed afterwards.'))
            ->schema([
                Select::make('academic_session_id')
                    ->label('Into session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->default(fn () => AcademicSession::query()->latestFirst()->value('id'))
                    ->required(),
                Radio::make('mode')
                    ->label('Class')
                    ->options([
                        'next' => 'Next class (by class position), keeping the same section letter where it exists',
                        'same' => 'Same class again (repeating the year)',
                        'specific' => 'A specific class and section',
                    ])
                    ->default('next')
                    ->required()
                    ->live(),
                ...array_map(
                    fn ($field) => $field->visible(fn (Get $get) => $get('mode') === 'specific')->required(fn (Get $get) => $field->getName() === 'academic_class_id' && $get('mode') === 'specific'),
                    static::classAndSectionFields(),
                ),
            ])
            ->action(function (Collection $records, array $data) {
                abort_unless(static::allowed(), 403);

                $result = app(Enrollments::class)->promoteMany(
                    $records,
                    AcademicSession::findOrFail($data['academic_session_id']),
                    $data['mode'],
                    $data['mode'] === 'specific' ? AcademicClass::findOrFail($data['academic_class_id']) : null,
                    $data['mode'] === 'specific' ? static::section($data) : null,
                );

                $skipped = collect($result['skipped'])->map(fn ($reason, $name) => e("{$name}: {$reason}"));

                Notification::make()
                    ->title("{$result['moved']} of {$records->count()} students moved")
                    ->body($skipped->isEmpty() ? null : new HtmlString('<strong>Not moved:</strong><br>'.$skipped->take(15)->implode('<br>').($skipped->count() > 15 ? '<br>…and '.($skipped->count() - 15).' more' : '')))
                    ->status($skipped->isEmpty() ? 'success' : ($result['moved'] ? 'warning' : 'danger'))
                    ->persistent($skipped->isNotEmpty())
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function allowed(): bool
    {
        return auth()->user()?->can('changePlacement', Enrollment::class) ?? false;
    }

    private static function leaveAction(string $name, string $label, string $icon, string $done, string $description): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->visible(fn (Student $record) => static::allowed() && $record->currentEnrollment)
            ->modalDescription($description)
            ->schema([
                DatePicker::make('ended_on')->label('Date')->default(today())->required(),
                Textarea::make('notes')->label('Notes (optional)')->rows(2)->maxLength(1000),
            ])
            ->action(fn (Student $record, array $data, Action $action) => static::run($action, $done, fn (Enrollments $enrollments) => $enrollments->{$name}(
                $record,
                Carbon::parse($data['ended_on']),
                $data['notes'] ?? null,
            )));
    }

    private static function run(Action $action, string $done, \Closure $change): void
    {
        abort_unless(static::allowed(), 403);

        try {
            $change(app(Enrollments::class));
        } catch (EnrollmentException $exception) {
            Notification::make()->danger()->title('Not changed')->body($exception->getMessage())->send();

            $action->halt();
        }

        Notification::make()->success()->title($done)->send();
    }

    private static function sessionField(): Select
    {
        return Select::make('academic_session_id')
            ->label('Session')
            ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
            ->default(fn () => AcademicSession::current()?->id)
            ->required();
    }

    /**
     * @return array<Select>
     */
    public static function classAndSectionFields(): array
    {
        return [
            Select::make('academic_class_id')
                ->label('Class')
                ->options(fn () => AcademicClass::query()->ordered()->pluck('name', 'id'))
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),
            Select::make('section_id')
                ->label('Section')
                ->options(fn (Get $get) => filled($get('academic_class_id'))
                    ? Section::query()->where('academic_class_id', $get('academic_class_id'))->orderBy('name')->pluck('name', 'id')
                    : [])
                ->placeholder('No section'),
        ];
    }

    private static function section(array $data): ?Section
    {
        return filled($data['section_id'] ?? null) ? Section::findOrFail($data['section_id']) : null;
    }

    // Sessions a student can be promoted into: those starting after their current one
    private static function laterSessions(?Enrollment $current): Collection
    {
        if (! $current) {
            return collect();
        }

        return AcademicSession::query()
            ->whereDate('starts_on', '>', $current->academicSession->starts_on)
            ->orderBy('starts_on')
            ->pluck('name', 'id');
    }
}
