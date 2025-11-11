<?php

namespace App\Filament\Resources\WhiskyBeerTasteResource\Pages;

use App\Filament\Resources\WhiskyBeerTasteResource;
use App\Models\WhiskyTasteGroup;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;

class ViewWhiskyTasteGroup extends ViewRecord
{
    protected static string $resource = WhiskyBeerTasteResource::class;

    protected static ?string $title = 'Просмотр группы вкусов';

    protected static ?string $model = WhiskyTasteGroup::class;

    /**
     * Поля карточки просмотра
     */
    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('name')
                    ->label('Название группы'),
                TextEntry::make('type')
                    ->label('Тип напитка'),
            ]);
    }

    /**
     * Подключаем связанные вкусы
     */
    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Resources\WhiskyBeerTasteResource\RelationManagers\TastesRelationManager::class,
        ];
    }
}
