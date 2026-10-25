<?php

namespace App\Filament\Resources\AcademicSessions\Pages;

use App\Filament\Resources\AcademicSessions\AcademicSessionResource;
use App\Models\AcademicSession;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicSession extends CreateRecord
{
    protected static string $resource = AcademicSessionResource::class;

    protected function fillForm(): void
    {
        $suggested = AcademicSession::suggestedNext();

        $this->form->fill([
            'name' => $suggested['name'],
            'starts_on' => $suggested['starts_on']->toDateString(),
            'ends_on' => $suggested['ends_on']->toDateString(),
            'make_current' => AcademicSession::current() === null,
        ]);
    }

    protected function afterCreate(): void
    {
        // The first session always becomes current, so enrollment always has a default
        if (($this->form->getRawState()['make_current'] ?? false) || AcademicSession::current() === null) {
            $this->getRecord()->makeCurrent();
        }
    }
}
