<?php

namespace App\Filament\Resources\MenuBlockResource\Pages;

use App\Filament\Resources\MenuBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuBlocks extends ListRecords
{
    protected static string $resource = MenuBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
