<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhiskyBeerTasteResource\Pages;
use Filament\Resources\Resource;

class WhiskyBeerTasteResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $model = \App\Models\WhiskyTasteGroup::class; // ✅ главная модель — группы вкусов
    protected static ?string $navigationLabel = 'Виски – Крепкие напитки – Пиво – вкусы и группы';

    // ❌ удаляем весь метод getModel()

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWhiskyBeerTastes::route('/'),
            'view' => Pages\ViewWhiskyTasteGroup::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\WhiskyBeerTasteResource\RelationManagers\TastesRelationManager::class,
        ];
    }
}
