<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('collection_id')
                ->label('Подборка')
                ->relationship('collections', 'name')
                ->searchable()
                ->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Подборка'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Добавить в подборку')
                    ->preloadRecordSelect()
                    ->recordTitleAttribute('name')
                    ->recordSelect(fn (Forms\Components\Select $select) =>
                    $select->label('Подборка')->searchable()->preload()
                    ),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
