<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\Actions\PlacementActions;
use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PlacementActions::group(),
            EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }
}
