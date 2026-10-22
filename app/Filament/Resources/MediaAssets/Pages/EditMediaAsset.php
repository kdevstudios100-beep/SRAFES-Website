<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditMediaAsset extends EditRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave()
    {
        if ($this->data['filename']) {
            $oldFilename = str($this->data['path'])->after('images/');

            Storage::disk('assets')->delete($oldFilename);
            Storage::disk('assets')->move($this->getRecord()->filename, $oldFilename);

            $this->redirect(MediaAssetResource::getUrl('edit', ['record' => $this->getRecord()]));
        }
    }
}
