<?php

namespace App\Filament\Resources\SommelierGroupResource\Pages;

use App\Filament\Resources\SommelierGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSommelierGroups extends ListRecords
{
    protected static string $resource = SommelierGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
