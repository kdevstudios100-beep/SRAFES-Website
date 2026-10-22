<?php

namespace App\Filament\Resources\ExaminationResults\Pages;

use App\Filament\Resources\ExaminationResults\ExaminationResultResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExaminationResult extends CreateRecord
{
    protected static string $resource = ExaminationResultResource::class;
}
