<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturerResource\Pages;
use App\Models\Manufacturer;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class ManufacturerResource extends Resource
{
    protected static ?string $model = Manufacturer::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.references');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.manufacturer.plural');
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TranslatableContainer::make(
                TextInput::make('name')
                    ->label('Название')
                    ->required(),
            ),
            Forms\Components\Select::make('region_id')
                ->relationship('region', 'name')
                ->label('Регион')
                ->searchable(),
            TextInput::make('website')->label('Веб-сайт'),
            TextInput::make('email')->email(),
            TextInput::make('phone'),
            Textarea::make('description')->label('Описание')->rows(3),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('name')->label('Название')->searchable(),
            Tables\Columns\TextColumn::make('region.name')->label('Регион'),
            TextColumn::make('website')->label('Сайт'),
            TextColumn::make('email'),
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
            'index' => Pages\ListManufacturers::route('/'),
            'create' => Pages\CreateManufacturer::route('/create'),
            'edit' => Pages\EditManufacturer::route('/{record}/edit'),
        ];
    }
}
