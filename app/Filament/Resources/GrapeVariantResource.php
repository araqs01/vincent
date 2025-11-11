<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GrapeVariantResource\Pages;
use App\Models\GrapeVariant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use App\Models\Taste;
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

            Section::make('Основная информация')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\Select::make('grape_id')
                            ->relationship('grape', 'name')
                            ->label('Сорт винограда')
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('region_id')
                            ->relationship('region', 'name')
                            ->label('Регион')
                            ->searchable(),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Категория')
                            ->searchable(),
                    ]),
                    Grid::make(4)->schema([
                        Forms\Components\TextInput::make('meta.wine_type')->label('Тип вина / игристого'),
                        Forms\Components\TextInput::make('meta.color')->label('Цвет'),
                        Forms\Components\TextInput::make('meta.blend')->label('Купаж'),
                        Forms\Components\TextInput::make('meta.series')->label('Серия'),
                    ]),
                ])->collapsible(),

            Section::make('Характеристики вкуса')
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
                ])->collapsible(),

            Section::make('Технические параметры')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('meta.sugar')->label('Сахар'),
                        Forms\Components\TextInput::make('meta.strength_min')->numeric()->label('Мин. крепость'),
                        Forms\Components\TextInput::make('meta.age_min')->numeric()->label('Мин. возраст'),
                        Forms\Components\TextInput::make('meta.oak_aging')->label('Выдержка в дубе'),
                        Forms\Components\TextInput::make('meta.storage_potential')->label('Потенциал хранения'),
                    ]),
                ])->collapsible(),

            Section::make('Дополнительные характеристики')
                ->schema([
                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('meta.main_taste')->label('Основной вкус'),
                        Forms\Components\TextInput::make('meta.aging')->label('Выдержка'),
                        Forms\Components\Textarea::make('meta.similar_wines')->label('Похожие вина')->rows(2),
                        Forms\Components\Textarea::make('meta.similar_grapes')->label('Похожие сорта')->rows(2),
                    ]),
                ])->collapsible(),

            Section::make('Вкусовой профиль')
                ->schema([
                    Select::make('tastes')
                        ->label('Вкусы сорта')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        // Загружаем все варианты вкусов
                        ->options(function () {
                            return Taste::orderBy('name->ru')
                                ->get()
                                ->pluck('name', 'id');
                        })
                        // При открытии записи — выставляем порядок по pivot (order_index)
                        ->afterStateHydrated(function (Select $component, $record) {
                            if (! $record) {
                                return;
                            }

                            $ids = $record->tastes()
                                ->orderByPivot('order_index')
                                ->pluck('tastes.id')
                                ->toArray();

                            $component->state($ids);
                        })
                        // При сохранении вручную фиксируем порядок
                        ->dehydrateStateUsing(fn($state) => $state)
                        ->saveRelationshipsUsing(function (Select $component, $state, $record) {
                            if (! $record) return;

                            $record->tastes()->sync(
                                collect($state)->mapWithKeys(
                                    fn($id, $i) => [$id => ['order_index' => $i + 1]]
                                )->all()
                            );
                        }),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('grape.name')->label('Сорт')->searchable(),
                Tables\Columns\TextColumn::make('region.name')->label('Регион'),
                Tables\Columns\TextColumn::make('category.name')->label('Категория'),
                Tables\Columns\TextColumn::make('meta.wine_type')->label('Тип вина'),
                Tables\Columns\TextColumn::make('meta.color')->label('Цвет'),
                Tables\Columns\TextColumn::make('meta.body')->label('Полнотелость')->sortable(),
                Tables\Columns\TextColumn::make('meta.acidity')->label('Кислотность')->sortable(),
                Tables\Columns\TextColumn::make('meta.sweetness')->label('Сладость')->sortable(),
            ])
            ->defaultSort('id', 'asc')
            ->filters([])
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
            'index'  => Pages\ListGrapeVariants::route('/'),
            'create' => Pages\CreateGrapeVariant::route('/create'),
            'edit'   => Pages\EditGrapeVariant::route('/{record}/edit'),
        ];
    }
}
