<?php

namespace App\Filament\Resources\AcademicClasses\RelationManagers;

use App\Enum\EnrollmentStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('A')
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->where('academic_class_id', $this->getOwnerRecord()->getKey())),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->description('Leave a class without sections if it is taught as one group.')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('active_enrollments_count')
                    ->label('Students enrolled')
                    ->counts(['enrollments as active_enrollments_count' => fn (Builder $query) => $query->where('status', EnrollmentStatus::Active)]),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // Hidden by SectionPolicy once any student has been placed in the section
                DeleteAction::make(),
            ]);
    }
}
