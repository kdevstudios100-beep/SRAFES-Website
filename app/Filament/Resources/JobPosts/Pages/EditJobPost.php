<?php

namespace App\Filament\Resources\JobPosts\Pages;

use App\Filament\Resources\JobPosts\JobPostResource;
use App\Models\JobPost;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditJobPost extends EditRecord
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Actions\ViewAction::make()
                ->icon(Heroicon::OutlinedEye)
                ->url(fn (JobPost $record) => route('careers.show', $record), true),
        ];
    }
}
