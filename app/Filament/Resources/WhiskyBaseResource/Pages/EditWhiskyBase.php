<?php

namespace App\Filament\Resources\WhiskyBaseResource\Pages;

use App\Filament\Resources\WhiskyBaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhiskyBase extends EditRecord
{
    protected static string $resource = WhiskyBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
