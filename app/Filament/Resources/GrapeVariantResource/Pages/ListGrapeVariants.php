<?php

namespace App\Filament\Resources\GrapeVariantResource\Pages;

use App\Filament\Resources\GrapeVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrapeVariants extends ListRecords
{
    protected static string $resource = GrapeVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
