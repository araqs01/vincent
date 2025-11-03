<?php

namespace App\Filament\Resources\GrapeResource\Pages;

use App\Filament\Resources\GrapeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrape extends EditRecord
{
    protected static string $resource = GrapeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
