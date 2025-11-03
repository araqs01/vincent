<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class GrapeVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'grapeVariants';
    protected static ?string $title = 'Сорта винограда (варианты)';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('grape.name')
                    ->label('Сорт винограда'),
                TextColumn::make('region.name')
                    ->label('Регион варианта')
                    ->toggleable(),
                TextColumn::make('pivot.percent')
                    ->label('Процент')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('pivot.main')
                    ->label('Главный сорт')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
            ])
            ->defaultSort('product_grape_variant.percent', 'desc');;
    }
}
