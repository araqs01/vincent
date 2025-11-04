<?php

namespace App\Filament\Resources\PairingGroupResource\Pages;

use App\Filament\Resources\PairingGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPairingGroups extends ListRecords
{
    protected static string $resource = PairingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
