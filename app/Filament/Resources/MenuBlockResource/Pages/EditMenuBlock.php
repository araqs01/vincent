<?php

namespace App\Filament\Resources\MenuBlockResource\Pages;

use App\Filament\Resources\MenuBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenuBlock extends EditRecord
{
    protected static string $resource = MenuBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
