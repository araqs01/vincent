<?php

namespace App\Filament\Resources\WhiskyDishResource\Pages;

use App\Filament\Resources\WhiskyDishResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhiskyDishes extends ListRecords
{
    protected static string $resource = WhiskyDishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
