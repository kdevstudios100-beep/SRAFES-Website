<?php

namespace App\Filament\Resources\ExaminationResults\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ExaminationResults\ExaminationResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExaminationResults extends ListRecords
{
    protected static string $resource = ExaminationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
