<?php

namespace App\Filament\Resources\GrapeVariantResource\Pages;

use App\Filament\Resources\GrapeVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrapeVariant extends EditRecord
{
    protected static string $resource = GrapeVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
