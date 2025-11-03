<?php

namespace App\Filament\Resources\CategoryFilterResource\Pages;

use App\Filament\Resources\CategoryFilterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryFilter extends EditRecord
{
    protected static string $resource = CategoryFilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
