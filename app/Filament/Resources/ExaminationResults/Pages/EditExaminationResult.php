<?php

namespace App\Filament\Resources\ExaminationResults\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ExaminationResults\ExaminationResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExaminationResult extends EditRecord
{
    protected static string $resource = ExaminationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
