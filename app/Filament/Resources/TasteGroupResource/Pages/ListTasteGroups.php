<?php

namespace App\Filament\Resources\TasteGroupResource\Pages;

use App\Filament\Resources\TasteGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasteGroups extends ListRecords
{
    protected static string $resource = TasteGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
