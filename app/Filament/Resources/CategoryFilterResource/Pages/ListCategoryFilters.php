<?php

namespace App\Filament\Resources\CategoryFilterResource\Pages;

use App\Filament\Resources\CategoryFilterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryFilters extends ListRecords
{
    protected static string $resource = CategoryFilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
