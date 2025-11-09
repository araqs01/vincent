<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GrapeVariantResource\Pages;
use App\Models\GrapeVariant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class GrapeVariantResource extends Resource
{
    protected static ?string $model = GrapeVariant::class;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.grape_variant.plural');
    }

    public static function getLabel(): string
    {
        return __('app.grape_variant.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.grape_variant.plural');
    }
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('grape_id')
                ->relationship('grape', 'name')
                ->label('Сорт винограда')
                ->required(),
            Forms\Components\Select::make('region_id')
                ->relationship('region', 'name')
                ->label('Регион')
                ->searchable(),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->label('Категория')
                ->searchable(),

            Section::make('Характеристики вина')
                ->description('Оцени по шкале от 0 до 5')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('meta.aromatic')->numeric()->minValue(0)->maxValue(5)->label('Ароматичность'),
                        Forms\Components\TextInput::make('meta.sweetness')->numeric()->minValue(0)->maxValue(5)->label('Сладость'),
                        Forms\Components\TextInput::make('meta.body')->numeric()->minValue(0)->maxValue(5)->label('Полнотелость'),
                        Forms\Components\TextInput::make('meta.tannin')->numeric()->minValue(0)->maxValue(5)->label('Танинность'),
                        Forms\Components\TextInput::make('meta.acidity')->numeric()->minValue(0)->maxValue(5)->label('Кислотность'),
                        Forms\Components\TextInput::make('meta.sparkling')->numeric()->minValue(0)->maxValue(5)->label('Игристость'),
                    ]),
                ]),
            Section::make('Вкусовой профиль')
                ->description('Выбери вкусы, характерные для этого сорта')
                ->schema([
                    Forms\Components\Select::make('tastes')
                        ->multiple()
                        ->relationship('tastes', 'name')
                        ->label('Вкусы сорта')
                        ->preload()
                        ->searchable(),
                ]),

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('grape.name')->label('Сорт'),
                Tables\Columns\TextColumn::make('region.name')->label('Регион'),
                Tables\Columns\TextColumn::make('category.name')->label('Категория'),
                Tables\Columns\TextColumn::make('meta')->label('Meta')->limit(60),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrapeVariants::route('/'),
            'create' => Pages\CreateGrapeVariant::route('/create'),
            'edit' => Pages\EditGrapeVariant::route('/{record}/edit'),
        ];
    }
}
