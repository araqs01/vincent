<?php

namespace App\Filament\Resources\SommelierGroupResource\Pages;

use App\Filament\Resources\SommelierGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSommelierGroup extends EditRecord
{
    protected static string $resource = SommelierGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
