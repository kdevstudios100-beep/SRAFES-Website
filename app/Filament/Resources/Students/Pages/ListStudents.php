<?php

namespace App\Filament\Resources\Students\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Students\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
