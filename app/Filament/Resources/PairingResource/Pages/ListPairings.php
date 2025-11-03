<?php

namespace App\Filament\Resources\PairingResource\Pages;

use App\Filament\Resources\PairingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPairings extends ListRecords
{
    protected static string $resource = PairingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
