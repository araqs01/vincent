<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DishResource\Pages;
use App\Models\Dish;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class DishResource extends Resource
{
    protected static ?string $model = Dish::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $label = 'Блюдо';
    protected static ?string $pluralLabel = 'Блюда';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.dish.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.dish.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),

                    Forms\Components\Select::make('dish_group_id')
                        ->label(__('app.dish.fields.group'))
                        ->relationship('group', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.dish.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('app.dish.fields.group'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Товаров'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDishes::route('/'),
            'create' => Pages\CreateDish::route('/create'),
            'edit' => Pages\EditDish::route('/{record}/edit'),
        ];
    }
}
