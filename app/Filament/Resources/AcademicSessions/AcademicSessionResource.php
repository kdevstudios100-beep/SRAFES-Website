<?php

namespace App\Filament\Resources\AcademicSessions;

use App\Enum\EnrollmentStatus;
use App\Filament\Resources\AcademicSessions\Pages\CreateAcademicSession;
use App\Filament\Resources\AcademicSessions\Pages\EditAcademicSession;
use App\Filament\Resources\AcademicSessions\Pages\ListAcademicSessions;
use App\Models\AcademicSession;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcademicSessionResource extends Resource
{
    protected static ?string $model = AcademicSession::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('2026-2027'),
                DatePicker::make('starts_on')
                    ->required()
                    ->live(),
                DatePicker::make('ends_on')
                    ->required()
                    ->after('starts_on')
                    // School years must not overlap, or a date could belong to two sessions
                    ->rule(fn (Get $get, ?AcademicSession $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        $overlapping = AcademicSession::query()
                            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                            ->whereDate('starts_on', '<=', $value)
                            ->whereDate('ends_on', '>=', $get('starts_on'))
                            ->value('name');

                        if ($overlapping) {
                            $fail("These dates overlap the {$overlapping} session.");
                        }
                    }),
                Toggle::make('make_current')
                    ->label('Make this the current session')
                    ->helperText('New enrollments and imports default to the current session.')
                    ->visibleOn('create')
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('starts_on')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_on')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean(),
                TextColumn::make('active_enrollments_count')
                    ->label('Students enrolled')
                    ->counts(['enrollments as active_enrollments_count' => fn (Builder $query) => $query->where('status', EnrollmentStatus::Active)]),
                TextColumn::make('enrollments_count')
                    ->label('All placements')
                    ->counts('enrollments')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                static::makeCurrentAction(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function makeCurrentAction(): Action
    {
        return Action::make('makeCurrent')
            ->label('Make current')
            ->icon('heroicon-m-star')
            ->requiresConfirmation()
            ->modalDescription('New enrollments, promotions and imports will default to this session. Existing placements are not changed.')
            ->visible(fn (AcademicSession $record) => ! $record->is_current && auth()->user()->can('update', $record))
            ->action(function (AcademicSession $record) {
                $record->makeCurrent();

                Notification::make()->success()->title("{$record->name} is now the current session")->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicSessions::route('/'),
            'create' => CreateAcademicSession::route('/create'),
            'edit' => EditAcademicSession::route('/{record}/edit'),
        ];
    }
}
