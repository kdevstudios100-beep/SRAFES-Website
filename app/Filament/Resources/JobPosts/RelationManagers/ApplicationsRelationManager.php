<?php

namespace App\Filament\Resources\JobPosts\RelationManagers;

use App\Models\JobApplication;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->heading('Job Applications')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->date(),
            ])
            ->recordActions([
                MediaAction::make('Resume')
                    ->modalWidth(Width::ScreenExtraLarge)
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->visible(fn (JobApplication $record) => filled($record->getFirstMediaUrl('resume')))
                    ->media(fn (JobApplication $record) => $record->getFirstMedia('resume')->getTemporaryUrl(now()->addMinutes(5))),
                MediaAction::make('Cover Letter')
                    ->modalWidth(Width::ScreenExtraLarge)
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->visible(fn (JobApplication $record) => filled($record->getFirstMediaUrl('cover_letter')))
                    ->media(fn (JobApplication $record) => $record->getFirstMedia('cover_letter')->getTemporaryUrl(now()->addMinutes(5))),
            ]);
    }
}
