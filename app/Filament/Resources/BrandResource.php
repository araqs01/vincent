<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Region;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.references');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.brand.plural');
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TranslatableContainer::make(
            TextInput::make('name')
                ->label('Название'),
            ),

            TextInput::make('slug')
                ->label('Slug')
                ->required(),

            Forms\Components\Select::make('region_id')
                ->relationship('region', 'name')
                ->label('Регион')
                ->searchable(),

            TranslatableContainer::make(
            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->rows(3),
            ),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Название'),
                Tables\Columns\TextColumn::make('region.name')->label('Регион'),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Кол-во товаров'),
            ])
            ->filters([
                SelectFilter::make('region_id')
                    ->label('Регион')
                    ->options(Region::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->placeholder('Все Регионы'),
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
