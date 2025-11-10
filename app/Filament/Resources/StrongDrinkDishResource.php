<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StrongDrinkDishResource\Pages;
use App\Models\StrongDrinkDish;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class StrongDrinkDishResource extends Resource
{
    protected static ?string $model = StrongDrinkDish::class;

    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Крепкие напитки – блюда';
    protected static ?string $pluralModelLabel = 'Блюда крепких напитков';
    protected static ?string $modelLabel = 'Блюдо крепких напитков';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
           TranslatableContainer::make(
                Forms\Components\TextInput::make('type')
                    ->label('Тип напитка')
                    ->required()
                    ->placeholder('например: Коньяк, Арманьяк, Бренди'),
            ),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('age')
                    ->label('Возраст')
                    ->placeholder('до 3 лет, старше 10 и т.д.'),

                Forms\Components\TextInput::make('class')
                    ->label('Класс')
                    ->placeholder('VS, VSOP, XO'),

                Forms\Components\TextInput::make('strength')
                    ->label('Крепость')
                    ->placeholder('например: 40%'),
            ]),

            Forms\Components\TextInput::make('drink_type')
                ->label('Тип напитка')
                ->placeholder('например: бренди, ликёр, пиво'),

            Forms\Components\TagsInput::make('taste_tags')
                ->label('Теги вкуса')
                ->placeholder('например: абрикос, сливочный, шоколадный'),

            Forms\Components\Textarea::make('dishes')
                ->label('Блюда')
                ->placeholder('Список блюд через запятую')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип напитка')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('class')
                    ->label('Класс')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('age')
                    ->label('Возраст')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('strength')
                    ->label('Крепость')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dishes')
                    ->label('Блюда')
                    ->limit(60),

                Tables\Columns\TextColumn::make('taste_tags')
                    ->label('Теги вкуса')
                    ->limit(40),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип напитка')
                    ->options(
                        StrongDrinkDish::query()
                            ->select('type')
                            ->distinct()
                            ->get()
                            ->pluck('type', 'type')
                            ->map(fn($t) => is_array($t) ? ($t['ru'] ?? reset($t)) : $t)
                            ->toArray()
                    ),
            ])
            ->defaultSort('id', 'asc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStrongDrinkDishes::route('/'),
            'create' => Pages\CreateStrongDrinkDish::route('/create'),
            'edit' => Pages\EditStrongDrinkDish::route('/{record}/edit'),
        ];
    }
}
