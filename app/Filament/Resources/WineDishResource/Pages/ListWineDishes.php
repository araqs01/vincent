<?php

namespace App\Filament\Resources\WineDishResource\Pages;

use App\Filament\Resources\WineDishResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWineDishes extends ListRecords
{
    protected static string $resource = WineDishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
