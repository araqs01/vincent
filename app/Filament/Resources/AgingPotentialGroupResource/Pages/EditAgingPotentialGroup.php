<?php

namespace App\Filament\Resources\AgingPotentialGroupResource\Pages;

use App\Filament\Resources\AgingPotentialGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgingPotentialGroup extends EditRecord
{
    protected static string $resource = AgingPotentialGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
