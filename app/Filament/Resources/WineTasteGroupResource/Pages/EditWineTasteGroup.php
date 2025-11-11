<?php

namespace App\Filament\Resources\WineTasteGroupResource\Pages;

use App\Filament\Resources\WineTasteGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWineTasteGroup extends EditRecord
{
    protected static string $resource = WineTasteGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
