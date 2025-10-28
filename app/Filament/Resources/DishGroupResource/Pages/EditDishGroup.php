<?php

namespace App\Filament\Resources\DishGroupResource\Pages;

use App\Filament\Resources\DishGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDishGroup extends EditRecord
{
    protected static string $resource = DishGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
