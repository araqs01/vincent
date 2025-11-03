<?php


namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants'; // связь из модели Product

    protected static ?string $title = 'Варианты';
    protected static ?string $pluralModelLabel = 'Варианты';
    protected static ?string $modelLabel = 'Вариант';
    protected static ?string $icon = 'heroicon-o-rectangle-stack';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('volume')
                            ->label('Объем (л)')
                            ->numeric()
                            ->placeholder('например 0.75'),

                        TextInput::make('vintage')
                            ->label('Год (винтаж)')
                            ->numeric()
                            ->placeholder('например 2018'),

                        TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->prefix('₽'),

                        TextInput::make('final_price')
                            ->label('Финальная цена')
                            ->numeric()
                            ->prefix('₽'),

                        TextInput::make('sku')
                            ->label('Артикул (SKU)')
                            ->maxLength(50),

                        TextInput::make('barcode')
                            ->label('Штрихкод')
                            ->maxLength(50),

                        TextInput::make('stock')
                            ->label('Количество на складе')
                            ->numeric(),
                    ])
                    ->columns(2),

                Section::make('Дополнительно')
                    ->schema([
                        KeyValue::make('meta')
                            ->label('Доп. параметры')
                            ->addButtonLabel('Добавить параметр')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значение'),
                    ]),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('volume')->label('Объем'),
                Tables\Columns\TextColumn::make('vintage')->label('Год'),
                Tables\Columns\TextColumn::make('price')->label('Цена')->money('RUB', true),
                Tables\Columns\TextColumn::make('final_price')->label('Финальная цена')->money('RUB', true),
                Tables\Columns\TextColumn::make('stock')->label('Остаток'),
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Добавить вариант'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
