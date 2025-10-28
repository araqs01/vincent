<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class DishesRelationManager extends RelationManager
{
    protected static string $relationship = 'dishes';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('dish_id')
                ->label('Блюдо')
                ->relationship('dishes', 'name')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('match_percent')
                ->label('Сочетаемость (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Блюдо'),
                Tables\Columns\TextColumn::make('pivot.match_percent')->label('Сочетаемость (%)'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Добавить блюдо')
                    ->preloadRecordSelect()
                    ->recordTitleAttribute('name')
                    ->recordSelect(fn (Forms\Components\Select $select) =>
                    $select->label('Блюдо')->searchable()->preload()
                    )
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        Forms\Components\TextInput::make('match_percent')
                            ->label('Сочетаемость (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
