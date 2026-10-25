<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Filament\Resources\ExaminationResults\ExaminationResultResource;
use App\Models\ExaminationResult;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExaminationResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'examinationResults';

    protected static ?string $title = 'Examination results';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('year'),
                TextColumn::make('detail'),
                TextColumn::make('academicClass.name')->label('Class')->placeholder('-'),
                TextColumn::make('obtained_marks')
                    ->label('Marks')
                    ->formatStateUsing(fn (ExaminationResult $record) => "{$record->obtained_marks} / {$record->total_marks} ({$record->percentage}%)"),
                TextColumn::make('grade')->placeholder('-'),
                TextColumn::make('status')->label('Result')->badge(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (ExaminationResult $record) => ExaminationResultResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (ExaminationResult $record) => auth()->user()->can('update', $record)),
            ]);
    }
}
