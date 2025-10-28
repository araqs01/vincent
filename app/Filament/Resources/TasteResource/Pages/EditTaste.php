<?php

namespace App\Filament\Resources\TasteResource\Pages;

use App\Filament\Resources\TasteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaste extends EditRecord
{
    protected static string $resource = TasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
