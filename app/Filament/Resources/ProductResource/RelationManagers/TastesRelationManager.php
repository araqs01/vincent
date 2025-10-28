<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class TastesRelationManager extends RelationManager
{
    protected static string $relationship = 'tastes';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('taste_id')
                ->label('Вкус')
                ->relationship('tastes', 'name')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('intensity_percent')
                ->label('Интенсивность (%)')
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
                Tables\Columns\TextColumn::make('name')->label('Вкус'),
                Tables\Columns\TextColumn::make('pivot.intensity_percent')->label('Интенсивность (%)'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Добавить вкус')
                    ->preloadRecordSelect()
                    ->recordTitleAttribute('name')
                    ->recordSelect(fn (Forms\Components\Select $select) =>
                    $select->label('Вкус')->searchable()->preload()
                    )
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        Forms\Components\TextInput::make('intensity_percent')
                            ->label('Интенсивность (%)')
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
