<?php

namespace App\Filament\Resources\AcademicClasses\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AcademicClasses\AcademicClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademicClasses extends ListRecords
{
    protected static string $resource = AcademicClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
