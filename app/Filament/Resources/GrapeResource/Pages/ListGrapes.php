<?php

namespace App\Filament\Resources\GrapeResource\Pages;

use App\Filament\Resources\GrapeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrapes extends ListRecords
{
    protected static string $resource = GrapeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
