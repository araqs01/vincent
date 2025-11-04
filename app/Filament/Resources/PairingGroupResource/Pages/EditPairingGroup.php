<?php

namespace App\Filament\Resources\PairingGroupResource\Pages;

use App\Filament\Resources\PairingGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPairingGroup extends EditRecord
{
    protected static string $resource = PairingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
