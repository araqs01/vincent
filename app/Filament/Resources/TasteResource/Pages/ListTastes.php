<?php

namespace App\Filament\Resources\TasteResource\Pages;

use App\Filament\Resources\TasteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTastes extends ListRecords
{
    protected static string $resource = TasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
