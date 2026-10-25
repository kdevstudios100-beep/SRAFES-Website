<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Pages\ImportStudents;
use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('gray')
                ->url(ImportStudents::getUrl())
                ->visible(fn () => ImportStudents::canAccess()),
            CreateAction::make(),
        ];
    }
}
