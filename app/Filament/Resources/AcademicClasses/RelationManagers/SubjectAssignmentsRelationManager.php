<?php

namespace App\Filament\Resources\AcademicClasses\RelationManagers;

use App\Models\SubjectAssignment;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjectAssignments';

    protected static ?string $title = 'Subjects';

    protected static ?string $modelLabel = 'subject';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->required()
                    // The unique index treats "no section" as distinct values, so check whole-class duplicates here
                    ->rule(fn (Get $get, ?SubjectAssignment $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        $exists = SubjectAssignment::query()
                            ->where('academic_class_id', $this->getOwnerRecord()->getKey())
                            ->where('subject_id', $value)
                            ->where(fn (Builder $query) => filled($get('section_id'))
                                ? $query->where('section_id', $get('section_id'))
                                : $query->whereNull('section_id'))
                            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                            ->exists();

                        if ($exists) {
                            $fail('This subject is already assigned here.');
                        }
                    }),
                Select::make('section_id')
                    ->label('Section')
                    ->options(fn () => $this->getOwnerRecord()->sections()->pluck('name', 'id'))
                    ->placeholder('Whole class')
                    ->helperText('Leave empty when every section of the class takes this subject.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['subject', 'section']))
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('subject.code')
                    ->label('Code')
                    ->placeholder('-'),
                TextColumn::make('section.name')
                    ->label('Section')
                    ->placeholder('Whole class'),
            ])
            ->filters([
                SelectFilter::make('section_id')
                    ->label('Section')
                    ->options(fn () => $this->getOwnerRecord()->sections()->pluck('name', 'id')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
