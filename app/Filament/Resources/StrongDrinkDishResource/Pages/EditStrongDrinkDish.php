<?php

namespace App\Filament\Resources\StrongDrinkDishResource\Pages;

use App\Filament\Resources\StrongDrinkDishResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStrongDrinkDish extends EditRecord
{
    protected static string $resource = StrongDrinkDishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
