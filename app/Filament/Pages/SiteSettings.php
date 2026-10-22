<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SiteSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.site-settings';

    public array $data = [];

    public function mount()
    {
        $this->form->fill(SiteSetting::firstOrCreate()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(SiteSetting::firstOrCreate())
            ->statePath('data')
            ->columns(2)
            ->components([
                TextInput::make('settings.name')
                    ->label('Site Name'),
                TextInput::make('settings.address'),
                TextInput::make('settings.email'),
                TextInput::make('settings.phone'),
                TextInput::make('settings.whatsapp')
                    ->label('WhatsApp'),
                TextInput::make('settings.instagram_url'),
                TextInput::make('settings.facebook_url'),
                TextInput::make('settings.x_url')
                    ->label('X/Twitter url'),
                TextInput::make('settings.google_maps_embed_url'),
                TextInput::make('settings.home_page_video_url'),
                Repeater::make('gallery_collections')
                    ->label('Gallery Collections')
                    ->columnSpanFull()
                    ->collapsible()
                    ->addAction(function (Action $action) {
                        return $action
                            ->label('Add Gallery Collection')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Collection Name')
                                    ->required()
                                    ->live(),
                            ])
                            ->action(function (SiteSetting $record, array $data) {
                                $collection = $record->gallery_collections ?? collect([]);

                                $collection->push($data);

                                $record->update(['gallery_collections' => $collection]);

                                $this->form->fill($record->toArray());
                            });
                    })
                    ->components([
                        TextInput::make('name')
                            ->label('Collection Name')
                            ->required()
                            ->live(),
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label('Images')
                            ->image()
                            ->directory('gallery')
                            ->disk('public')
                            ->collection(fn (Get $get) => $get('name'))
                            ->multiple(),
                    ]),
            ]);
    }

    public function save()
    {
        $state = $this->form->getState();

        $siteSetting = SiteSetting::firstOrCreate();

        $siteSetting->update($state);

        $this->form->model($siteSetting)->saveRelationships();

        Notification::make()
            ->success()
            ->title('Site settings were saved!')
            ->send();
    }
}
