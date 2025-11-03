<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class PairingsRelationManager extends RelationManager
{
    protected static string $relationship = 'pairings'; // 👈 имя связи из модели Product
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Гастрономические сочетания';

    public  function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required(),
        ]);
    }

    public  function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->formatStateUsing(fn($state) => is_array($state) ? ($state['ru'] ?? $state['en'] ?? '') : $state),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Добавить существующее сочетание'),

                Tables\Actions\CreateAction::make()
                    ->label('Создать новое сочетание'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
