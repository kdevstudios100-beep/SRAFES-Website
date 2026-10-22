<?php

namespace App\Filament\Resources\JobPosts;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\JobPosts\Pages\ListJobPosts;
use App\Filament\Resources\JobPosts\Pages\CreateJobPost;
use App\Filament\Resources\JobPosts\Pages\EditJobPost;
use App\Filament\Resources\JobPostResource\Pages;
use App\Filament\Resources\JobPosts\RelationManagers\ApplicationsRelationManager;
use App\Models\JobPost;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobPostResource extends Resource
{
    protected static ?string $model = JobPost::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->columnSpanFull()
                            ->required(),
                    ]),
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('salary_range')
                            ->maxLength(255),
                        DatePicker::make('application_deadline')
                            ->required(),
                        Toggle::make('is_active')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('salary_range')
                    ->searchable(),
                TextColumn::make('application_deadline')
                    ->searchable(),
                BooleanColumn::make('is_active')
                    ->searchable(),
                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->badge(),
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
            ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobPosts::route('/'),
            'create' => CreateJobPost::route('/create'),
            'edit' => EditJobPost::route('/{record}/edit'),
        ];
    }
}
