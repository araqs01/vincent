<?php

namespace App\Filament\Resources\WhiskyBeerTasteResource\Pages;

use App\Filament\Resources\WhiskyBeerTasteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhiskyBeerTaste extends EditRecord
{
    protected static string $resource = WhiskyBeerTasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
