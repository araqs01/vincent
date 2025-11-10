<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WineDishResource\Pages;
use App\Models\WineDish;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class WineDishResource extends Resource
{
    protected static ?string $model = WineDish::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Вино — Блюдо';
    protected static ?string $pluralModelLabel = 'Вино — Блюда';

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Основные сведения')->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('grapes')
                    ->multiple()
                    ->label('Сорт(а) винограда')
                    ->relationship('grapes', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('blend')
                    ->label('Купаж')
                    ->maxLength(255),

                TranslatableContainer::make(
                    Forms\Components\TextInput::make('name')
                        ->label('Название вина')
                        ->required()
                ),

                Forms\Components\TextInput::make('type')
                    ->label('Тип (Белое, Красное, Игристое)')
                    ->maxLength(100),

                Forms\Components\TextInput::make('color')
                    ->label('Цвет')
                    ->maxLength(100),

                Forms\Components\Select::make('region_id')
                    ->label('Регион')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload(),
            ]),

            Section::make('Характеристики вина')
                ->description('Значения от 0 до 5')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('aromaticity')->numeric()->label('Ароматичность'),
                        Forms\Components\TextInput::make('sweetness')->numeric()->label('Сладость'),
                        Forms\Components\TextInput::make('body')->numeric()->label('Полнотелость'),
                        Forms\Components\TextInput::make('tannin')->numeric()->label('Танинность'),
                        Forms\Components\TextInput::make('acidity')->numeric()->label('Кислотность'),
                        Forms\Components\TextInput::make('effervescence')->numeric()->label('Игристость'),
                    ]),
                ]),

            Section::make('Параметры крепости и возраста')
                ->schema([
                    Grid::make(4)->schema([
                        Forms\Components\TextInput::make('strength_min')->numeric()->label('Мин. крепость (%)'),
                        Forms\Components\TextInput::make('strength_max')->numeric()->label('Макс. крепость (%)'),
                        Forms\Components\TextInput::make('age_min')->numeric()->label('Мин. возраст (лет)'),
                        Forms\Components\TextInput::make('age_max')->numeric()->label('Макс. возраст (лет)'),
                    ]),
                ]),

            Section::make('Дополнительные данные')->schema([
                Grid::make(3)->schema([
                    Forms\Components\TextInput::make('sugar')->label('Содержание сахара'),
                    Forms\Components\TextInput::make('price_min')->numeric()->label('Мин. цена'),
                    Forms\Components\TextInput::make('price_max')->numeric()->label('Макс. цена'),
                ]),

                Forms\Components\TextInput::make('extra_marker')
                    ->label('Доп. маркер')
                    ->maxLength(255),

                Forms\Components\Textarea::make('meta')
                    ->label('Meta (доп. данные)')
                    ->rows(3),
            ]),

            Section::make('Гастро-сочетания')->schema([
                TranslatableContainer::make(
                    Forms\Components\Textarea::make('pairings')
                        ->label('Гастро-сочетания')
                        ->rows(4)
                        ->helperText('Например: стейк, трюфель, рыба в беконе')
                )->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Категория')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Название')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Тип')->sortable(),
                Tables\Columns\TextColumn::make('color')->label('Цвет')->sortable(),
                Tables\Columns\TextColumn::make('grapes.name')->label('Сорт винограда')->sortable(),
                Tables\Columns\TextColumn::make('region.name')->label('Регион')->sortable(),
                Tables\Columns\TextColumn::make('pairings')->label('Гастро-сочетания')->limit(60)->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип вина')
                    ->options([
                        'Красное' => 'Красное',
                        'Белое' => 'Белое',
                        'Розовое' => 'Розовое',
                        'Игристое' => 'Игристое',
                    ]),
            ])
            ->defaultSort('id', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWineDishes::route('/'),
            'create' => Pages\CreateWineDish::route('/create'),
            'edit' => Pages\EditWineDish::route('/{record}/edit'),
        ];
    }
}
