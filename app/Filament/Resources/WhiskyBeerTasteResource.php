<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhiskyBeerTasteResource\Pages;
use Filament\Resources\Resource;

class WhiskyBeerTasteResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $model = null; // у нас нет единой модели
    protected static ?string $navigationLabel = 'Виски – Крепкие напитки – Пиво – вкусы и группы';

    public static function getModel(): string
    {
        // безопасный fallback
        return \App\Models\WhiskyTaste::class;
    }

    public static function getPages(): array
    {
        return [
            // ✅ теперь метод route() будет доступен
            'index' => Pages\ManageWhiskyBeerTastes::route('/'),
        ];
    }
}
