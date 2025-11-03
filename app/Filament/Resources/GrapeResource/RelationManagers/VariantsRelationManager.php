<?php

namespace App\Filament\Resources\GrapeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\RelationManagers\RelationManager;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Варианты сорта';

    public  function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('region_id')
                ->relationship('region', 'name')
                ->label('Регион')
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->label('Категория')
                ->searchable()
                ->preload(),

            Forms\Components\Textarea::make('meta')
                ->label('Характеристики (JSON)')
                ->rows(4)
                ->helperText('Например: {"sweetness": 3.2, "body": 2.8, "aromatic": 4.0}')
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('region.name')->label('Регион'),
                Tables\Columns\TextColumn::make('category.name')->label('Категория'),
                Tables\Columns\TextColumn::make('meta')->label('Meta')->limit(60),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime('d.m.Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Добавить вариант'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
            ]);
    }
}
