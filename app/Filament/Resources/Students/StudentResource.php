<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Actions\PlacementActions;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Resources\Students\RelationManagers\ExaminationResultsRelationManager;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Section;
use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                SchemaSection::make('Student details')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('admission_number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique for every student; used to find results and to detect duplicates.'),
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->maxLength(255),
                        TextInput::make('surname')
                            ->maxLength(255),
                        TextInput::make('father_name')
                            ->label('Father\'s name')
                            ->maxLength(255),
                        DatePicker::make('date_of_birth')
                            ->maxDate(today()),
                        Toggle::make('has_scholarship'),
                    ]),
                SchemaSection::make()
                    ->columnSpan(1)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Profile image')
                            ->imageEditor()
                            ->collection('avatar')
                            ->disk('filament'),
                    ]),
                // Class placement is chosen once on create; afterwards it changes only through the placement actions
                SchemaSection::make('Enrollment')
                    ->description('Optional. Leave the class empty to enroll the student later.')
                    ->visible(fn (string $operation) => $operation === 'create' && PlacementActions::allowed())
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        ...array_map(fn ($field) => $field->required(false), PlacementActions::classAndSectionFields()),
                        TextInput::make('roll_number')->maxLength(50),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                SchemaSection::make('Student details')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('admission_number')->copyable(),
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('surname')->placeholder('-'),
                        TextEntry::make('father_name')->label('Father\'s name')->placeholder('-'),
                        TextEntry::make('date_of_birth')->date()->placeholder('-'),
                        IconEntry::make('has_scholarship')->label('Scholarship')->boolean(),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state) => $state ? 'success' : 'gray'),
                        TextEntry::make('id')->label('Student ID'),
                    ]),
                SchemaSection::make()
                    ->columnSpan(1)
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('avatar')
                            ->collection('avatar')
                            ->hiddenLabel()
                            ->circular(),
                    ]),
                SchemaSection::make('Current placement')
                    ->columnSpan(2)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('currentEnrollment.academicSession.name')->label('Session')->placeholder('Not enrolled'),
                        TextEntry::make('currentEnrollment.placement')->label('Class / section')->placeholder('-'),
                        TextEntry::make('currentEnrollment.roll_number')->label('Roll number')->placeholder('-'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['currentEnrollment.academicSession', 'currentEnrollment.academicClass', 'currentEnrollment.section']))
            ->defaultSort('id', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->searchPlaceholder('Name, father\'s name or admission number')
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatar')
                    ->label('')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('admission_number')
                    ->label('Admission no.')
                    ->copyable()
                    ->icon('heroicon-m-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->iconColor('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Name')
                    ->formatStateUsing(fn (Student $record) => $record->name)
                    ->searchable(['first_name', 'last_name', 'surname'])
                    ->sortable(['first_name', 'last_name']),
                TextColumn::make('father_name')
                    ->label('Father\'s name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('currentEnrollment.placement')
                    ->label('Class / section')
                    ->placeholder('Not enrolled'),
                TextColumn::make('currentEnrollment.academicSession.name')
                    ->label('Session')
                    ->toggleable(),
                TextColumn::make('currentEnrollment.roll_number')
                    ->label('Roll no.')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_of_birth')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),
                IconColumn::make('has_scholarship')
                    ->label('Scholarship')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('session')
                    ->label('Session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $query, $session) => $query->enrolledIn(academicSessionId: (int) $session))),
                SelectFilter::make('class')
                    ->label('Class')
                    ->options(fn () => AcademicClass::query()->ordered()->pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $query, $class) => $query->enrolledIn(academicClassId: (int) $class))),
                SelectFilter::make('section')
                    ->label('Section')
                    ->options(fn () => Section::query()->with('academicClass')->get()->sortBy(['academicClass.sort_order', 'name'])->mapWithKeys(fn (Section $section) => [$section->id => $section->full_name]))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $query, $section) => $query->enrolledIn(sectionId: (int) $section))),
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->default(true),
                TernaryFilter::make('has_scholarship')
                    ->label('Scholarship'),
                Filter::make('not_enrolled')
                    ->label('Not currently enrolled')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->whereDoesntHave('enrollments', fn (Builder $query) => $query->active())),
            ])
            ->recordUrl(fn (Student $record) => static::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    PlacementActions::bulkPromote(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EnrollmentsRelationManager::class,
            ExaminationResultsRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['admission_number', 'first_name', 'last_name', 'father_name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'view' => ViewStudent::route('/{record}'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
