<?php

namespace App\Filament\Resources\CategorySortGroupResource\Pages;

use App\Filament\Resources\CategorySortGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategorySortGroups extends ListRecords
{
    protected static string $resource = CategorySortGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
