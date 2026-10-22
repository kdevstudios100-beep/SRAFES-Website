<?php

namespace App\Filament\Resources\ExaminationResults;

use App\Enum\ExaminationResultStatus;
use App\Filament\Resources\ExaminationResults\Pages\CreateExaminationResult;
use App\Filament\Resources\ExaminationResults\Pages\EditExaminationResult;
use App\Filament\Resources\ExaminationResults\Pages\ListExaminationResults;
use App\Models\ExaminationResult;
use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExaminationResultResource extends Resource
{
    protected static ?string $model = ExaminationResult::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->relationship('student', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Student $student) => $student->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('year')
                    ->default(now()->year),
                TextInput::make('detail')
                    ->default('Annual Examination'),
                Select::make('academic_class_id')
                    ->relationship('academicClass', 'name'),
                TextInput::make('total_marks')
                    ->live()
                    ->numeric(),
                TextInput::make('obtained_marks')
                    ->live()
                    ->numeric(),
                Placeholder::make('percentage')
                    ->label('Percentage')
                    ->visibleOn('edit')
                    ->content(fn (ExaminationResult $record) => $record->percentage.'%'),
                ToggleButtons::make('status')
                    ->inline()
                    ->options(ExaminationResultStatus::class),
                TextInput::make('grade'),
                SpatieMediaLibraryFileUpload::make('result_file')
                    ->imageEditor()
                    ->downloadable()
                    ->collection('result_file')
                    ->disk('filament'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('student.avatar')
                    ->label('Profile image')
                    ->collection('avatar')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('student.name')
                    ->label('Student name')
                    ->searchable(['first_name', 'last_name'])
                    ->toggleable(),
                TextColumn::make('student.father_name')
                    ->label('Father\s name')
                    ->searchable(['father_name'])
                    ->toggleable(),
                TextColumn::make('student.admission_number')
                    ->label('Admission number')
                    ->visible(fn ($livewire) => $livewire::class === ListExaminationResults::class)
                    ->copyable()
                    ->icon('heroicon-m-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->iconColor('primary')
                    ->searchable(['admission_number'])
                    ->toggleable(),
                TextColumn::make('academicClass.name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('year')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('detail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('total_marks')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('obtained_marks')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Result')
                    ->badge()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListExaminationResults::route('/'),
            'create' => CreateExaminationResult::route('/create'),
            'edit' => EditExaminationResult::route('/{record}/edit'),
        ];
    }
}
