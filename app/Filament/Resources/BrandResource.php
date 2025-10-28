<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?string $modelLabel = 'Бренд';
    protected static ?string $pluralModelLabel = 'Бренды';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('name.ru')
                ->label('Название (RU)')
                ->required(),

            TextInput::make('name.en')
                ->label('Название (EN)'),

            TextInput::make('slug')
                ->label('Slug')
                ->required(),

            TextInput::make('country')
                ->label('Страна'),


            Forms\Components\Textarea::make('description.ru')
                ->label('Описание (RU)')
                ->rows(3),

            Forms\Components\Textarea::make('description.en')
                ->label('Описание (EN)')
                ->rows(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name.ru')->label('Название'),
                TextColumn::make('country')->label('Страна'),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Кол-во товаров'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
