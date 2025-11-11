<?php

namespace App\Filament\Resources\WineTasteGroupResource\Pages;

use App\Filament\Resources\WineTasteGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWineTasteGroups extends ListRecords
{
    protected static string $resource = WineTasteGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
