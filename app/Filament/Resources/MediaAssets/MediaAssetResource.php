<?php

namespace App\Filament\Resources\MediaAssets;

use App\Filament\Resources\MediaAssets\Pages\CreateMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\EditMediaAsset;
use App\Filament\Resources\MediaAssets\Pages\ListMediaAssets;
use App\Models\MediaAsset;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('path'),
                ...static::getSizeAndDimensionFields(),
                FileUpload::make('filename')
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->disk('assets')
                    ->required()
                    ->acceptedFileTypes(fn (Model $record) => ['image/'.$record->extension]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(MediaAsset::query())
            ->columns([
                Stack::make([
                    ImageColumn::make('url')
                        ->height('100%')
                        ->width('100%'),
                    Stack::make([
                        TextColumn::make('filename')
                            ->weight(FontWeight::Bold),
                        TextColumn::make('size')
                            ->prefix('Size: ')
                            ->suffix(' KB')
                            ->weight(FontWeight::Medium),
                        TextColumn::make('dimentions')
                            ->prefix('Dimentions: ')
                            ->weight(FontWeight::Medium),
                    ]),
                ])->space(3),
            ])
            ->recordActions([
                Action::make('visit')
                    ->label('Visit link')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (MediaAsset $record): string => $record->url)
                    ->openUrlInNewTab(),
                // EditAction::make(),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->fillForm(fn (MediaAsset $record) => ['path' => $record->filename])
                    ->schema(fn (MediaAsset $record, Schema $schema) => $schema
                        ->columns(2)
                        ->components([
                            ...static::getSizeAndDimensionFields(),
                            FileUpload::make('path')
                                ->columnSpanFull()
                                ->hiddenLabel()
                                ->disk('assets')
                                ->required()
                                ->acceptedFileTypes(['image/'.$record->extension]),
                        ]))
                    ->action(function (array $data, MediaAsset $record) {
                        Storage::disk('assets')->delete($record->filename);
                        Storage::disk('assets')->move($data['path'], $record->filename);
                    }),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->paginated(false);
    }

    public static function getSizeAndDimensionFields(): array
    {
        return [
            Placeholder::make('size')
                ->content(fn (MediaAsset $record) => $record->size.' KB')
                ->helperText('Always try to upload optimized/compressed media with less size for better page speed.'),
            Placeholder::make('dimentions')
                ->content(fn (MediaAsset $record) => $record->dimentions)
                ->helperText('When uploading media, make sure to match the dimensions for exact styling. Uploading images with random dimensions might affect the page styling.'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaAssets::route('/'),
            'create' => CreateMediaAsset::route('/create'),
            'edit' => EditMediaAsset::route('/{record}/edit'),
        ];
    }
}
