<?php

namespace App\Filament\Resources\PairingResource\Pages;

use App\Filament\Resources\PairingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPairing extends EditRecord
{
    protected static string $resource = PairingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
