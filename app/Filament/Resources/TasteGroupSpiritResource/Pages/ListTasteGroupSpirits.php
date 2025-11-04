<?php

namespace App\Filament\Resources\TasteGroupSpiritResource\Pages;

use App\Filament\Resources\TasteGroupSpiritResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasteGroupSpirits extends ListRecords
{
    protected static string $resource = TasteGroupSpiritResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
