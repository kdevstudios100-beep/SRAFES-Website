<?php

namespace App\Filament\Resources\AcademicClasses;

use App\Enum\EnrollmentStatus;
use App\Filament\Resources\AcademicClasses\Pages\CreateAcademicClass;
use App\Filament\Resources\AcademicClasses\Pages\EditAcademicClass;
use App\Filament\Resources\AcademicClasses\Pages\ListAcademicClasses;
use App\Filament\Resources\AcademicClasses\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\AcademicClasses\RelationManagers\SubjectAssignmentsRelationManager;
use App\Models\AcademicClass;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcademicClassResource extends Resource
{
    protected static ?string $model = AcademicClass::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'class';

    protected static ?string $pluralModelLabel = 'classes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                TextInput::make('sort_order')
                    ->label('Position')
                    ->helperText('Order of the class in the school, e.g. 1 for Class 1. Promotion moves students to the class with the next position.')
                    ->integer()
                    ->minValue(0)
                    ->maxValue(1000)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('sections'))
            ->defaultSort(fn (Builder $query) => $query->ordered())
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Position')
                    ->placeholder('Not set'),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('sections.name')
                    ->label('Sections')
                    ->badge()
                    ->placeholder('None'),
                TextColumn::make('active_enrollments_count')
                    ->label('Students enrolled')
                    ->counts(['enrollments as active_enrollments_count' => fn (Builder $query) => $query->where('status', EnrollmentStatus::Active)]),
                TextColumn::make('subject_assignments_count')
                    ->label('Subjects')
                    ->counts('subjectAssignments'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Per-record check so classes that hold history are skipped
                    DeleteBulkAction::make()->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
            SubjectAssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicClasses::route('/'),
            'create' => CreateAcademicClass::route('/create'),
            'edit' => EditAcademicClass::route('/{record}/edit'),
        ];
    }
}
