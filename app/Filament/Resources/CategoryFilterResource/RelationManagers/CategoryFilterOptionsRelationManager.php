<?php

namespace App\Filament\Resources\CategoryFilterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CategoryFilterOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';
    protected static ?string $title = 'Filter Options';

    public function form(Form $form): Form
    {
        return $form->schema([
            TranslatableContainer::make(
                Forms\Components\TextInput::make('value')
                    ->label(__('app.category_filter_option.fields.value'))
                    ->required()
            ),

            Forms\Components\TextInput::make('slug')
                ->label(__('app.category_filter_option.fields.slug'))
                ->nullable(),

            Forms\Components\KeyValue::make('meta')
                ->label(__('app.category_filter_option.fields.meta'))
                ->keyLabel('Param')
                ->valueLabel('Value'),

            Forms\Components\TextInput::make('order_index')
                ->label(__('app.common.order_index'))
                ->numeric()
                ->default(1),

            Forms\Components\Toggle::make('is_active')
                ->label(__('app.common.is_active'))
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('value')->label('Value'),
                Tables\Columns\TextColumn::make('slug')->label('Slug'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('order_index')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
