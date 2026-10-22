<?php

namespace App\Filament\Resources\JobPosts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\JobPosts\JobPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobPosts extends ListRecords
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
