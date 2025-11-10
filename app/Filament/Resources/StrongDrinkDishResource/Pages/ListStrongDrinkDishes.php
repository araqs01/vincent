<?php

namespace App\Filament\Resources\StrongDrinkDishResource\Pages;

use App\Filament\Resources\StrongDrinkDishResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStrongDrinkDishes extends ListRecords
{
    protected static string $resource = StrongDrinkDishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
