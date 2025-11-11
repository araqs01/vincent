<?php

namespace App\Filament\Resources\SommelierGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';
    protected static ?string $recordTitleAttribute = 'name';

    public  function form(\Filament\Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                TextInput::make('name.ru')
                    ->label('Название (RU)')
                    ->required(),
                TextInput::make('name.en')
                    ->label('Название (EN)'),
            ]),
            TextInput::make('slug')
                ->label('Slug')
                ->reactive()
                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('order_index')
                ->numeric()
                ->default(0)
                ->label('Порядок'),
        ]);
    }

    public  function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Название (RU)')->searchable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('order_index')->label('Порядок'),
            ])
            ->defaultSort('order_index')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
