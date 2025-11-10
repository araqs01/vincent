<?php

namespace App\Filament\Resources\WhiskyBaseResource\Pages;

use App\Filament\Resources\WhiskyBaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhiskyBases extends ListRecords
{
    protected static string $resource = WhiskyBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
