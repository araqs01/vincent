<?php

namespace App\Filament\Resources\TasteGroupSpiritResource\Pages;

use App\Filament\Resources\TasteGroupSpiritResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTasteGroupSpirit extends EditRecord
{
    protected static string $resource = TasteGroupSpiritResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
