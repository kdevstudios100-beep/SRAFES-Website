<?php

namespace App\Filament\Resources\Students;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->required(),
                        TextInput::make('last_name'),
                        TextInput::make('surname'),
                        TextInput::make('father_name')
                            ->label('Father\'s name'),
                        DatePicker::make('date_of_birth'),
                        TextInput::make('admission_number'),
                        Select::make('academic_class_id')
                            ->relationship('academicClass', 'name'),
                        Toggle::make('has_scholarship'),
                        Toggle::make('is_active'),
                    ]),
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Profile image')
                            ->imageEditor()
                            ->collection('avatar')
                            ->disk('filament'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatar')
                    ->label('Profile image')
                    ->circular(),
                TextColumn::make('first_name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('surname')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('father_name')
                    ->toggleable()
                    ->label('Father\'s name')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->toggleable()
                    ->date()
                    ->searchable(),
                TextColumn::make('admission_number')
                    ->toggleable()
                    ->copyable()
                    ->icon('heroicon-m-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->iconColor('primary')
                    ->searchable(),
                TextColumn::make('academicClass.name')
                    ->toggleable()
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->toggleable(),
                ToggleColumn::make('has_scholarship')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
