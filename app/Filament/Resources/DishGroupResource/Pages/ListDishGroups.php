<?php

namespace App\Filament\Resources\DishGroupResource\Pages;

use App\Filament\Resources\DishGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDishGroups extends ListRecords
{
    protected static string $resource = DishGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
