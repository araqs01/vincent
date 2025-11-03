<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GrapeResource\Pages;
use App\Models\Grape;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class GrapeResource extends Resource
{
    protected static ?string $model = Grape::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.grape.plural');
    }

    public static function getLabel(): string
    {
        return __('app.grape.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.grape.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->rows(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
            Tables\Columns\TextColumn::make('description')->label('Описание')->limit(50),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrapes::route('/'),
            'create' => Pages\CreateGrape::route('/create'),
            'edit' => Pages\EditGrape::route('/{record}/edit'),
        ];
    }
}
