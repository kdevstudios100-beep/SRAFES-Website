<?php

namespace App\Filament\Resources\AcademicClasses;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AcademicClasses\Pages\ListAcademicClasses;
use App\Filament\Resources\AcademicClasses\Pages\CreateAcademicClass;
use App\Filament\Resources\AcademicClasses\Pages\EditAcademicClass;
use App\Filament\Resources\AcademicClassResource\Pages;
use App\Models\AcademicClass;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicClassResource extends Resource
{
    protected static ?string $model = AcademicClass::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListAcademicClasses::route('/'),
            'create' => CreateAcademicClass::route('/create'),
            'edit' => EditAcademicClass::route('/{record}/edit'),
        ];
    }
}
