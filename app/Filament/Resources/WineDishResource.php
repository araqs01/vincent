<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WineDishResource\Pages;
use App\Filament\Resources\WineDishResource\RelationManagers;
use App\Models\WineDish;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('grape_id')
                    ->label('Сорт винограда')
                    ->relationship('grapes', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('blend')
                    ->label('Купаж')
                    ->maxLength(255),

                // ✅ Переводимое поле "Название"
                TranslatableContainer::make(
                    Forms\Components\TextInput::make('name')
                        ->label('Название вина')
                        ->required()
                ),

                Forms\Components\Select::make('region_id')
                    ->label('Регион')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('type')
                    ->label('Тип (Белое, Красное, Игристое)')
                    ->maxLength(100),

                // ✅ Переводимое поле "Гастро-сочетания"
                TranslatableContainer::make(
                    Forms\Components\Textarea::make('pairings')
                        ->label('Гастро-сочетания')
                        ->rows(4)
                        ->helperText('Например: стейк, трюфель, рыба в беконе')
                        ->maxLength(2000)
                )->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->sortable(),
                Tables\Columns\TextColumn::make('grapes.name')
                    ->label('Сорт винограда')
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Регион')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pairings')
                    ->label('Гастро-сочетания')
                    ->limit(60)
                    ->wrap(),
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
