<?php

namespace App\Filament\Resources\WhiskyBeerTasteResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TastesRelationManager extends RelationManager
{
    protected static string $relationship = 'tastes';

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Вкусы, принадлежащие этой группе';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TranslatableContainer::make(
                    Forms\Components\TextInput::make('name')
                        ->label('Название вкуса')
                        ->required()
                ),
                Forms\Components\TextInput::make('type')
                    ->label('Тип напитка')
                    ->required(),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->label('Вес'),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Название'),
                Tables\Columns\TextColumn::make('type')->label('Тип напитка'),
                Tables\Columns\TextColumn::make('weight')->label('Вес')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
