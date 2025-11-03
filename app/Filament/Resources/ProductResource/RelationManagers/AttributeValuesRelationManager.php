<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class AttributeValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributeValues';
    protected static ?string $recordTitleAttribute = 'attribute.name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('attribute_id')
                ->label('Атрибут')
                ->relationship('attribute', 'name')
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('value')
                ->label('Значение')
                ->required()
                ->maxLength(255),
        ]);
    }
    //Vkusery kpcnenq
    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attribute.name')->label('Атрибут'),
                Tables\Columns\TextColumn::make('value.ru')->label('Значение'),
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
