<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhiskyDishResource\Pages;
use App\Models\WhiskyDish;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class WhiskyDishResource extends Resource
{
    protected static ?string $model = WhiskyDish::class;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Виски — Блюда';
    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TranslatableContainer::make(
                TextInput::make('type')->label('Тип Виски')->required(),
            ),
            TranslatableContainer::make(
                TextInput::make('region')->label('Регион')->required(),
            ),
            Grid::make(4)->schema([
                Grid::make(4)->schema([
                    self::rangeField('sweetness', 'Сладость'),
                    self::rangeField('smokiness', 'Дымность'),
                    self::rangeField('fruitiness', 'Фруктовость'),
                    self::rangeField('strength', 'Крепость'),
                    self::rangeField('spiciness', 'Специи'),
                    self::rangeField('astringency', 'Терпкость'),
                    self::rangeField('body', 'Плотность'),
                    TextInput::make('age')
                        ->label('Возраст')
                        ->placeholder('например: 18+, не старше 18 лет'),
                ]),
                Textarea::make('tags')->label('Теги (через запятую)')->rows(2),
                Textarea::make('snacks')->label('Закуски (через запятую)')->rows(2),
            ])
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('type')->label('Тип Виски')->searchable(),
                TextColumn::make('region')->label('Регион')->searchable(),
                TextColumn::make('sweetness')->label('Сладость'),
                TextColumn::make('smokiness')->label('Дымность'),
                TextColumn::make('fruitiness')->label('Фруктовость'),
                TextColumn::make('body')->label('Плотность'),
                TextColumn::make('age')->label('Возраст'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'asc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhiskyDishes::route('/'),
            'create' => Pages\CreateWhiskyDish::route('/create'),
            'edit' => Pages\EditWhiskyDish::route('/{record}/edit'),
        ];
    }

    protected static function rangeField(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->placeholder('например: 2-5 или 3+')
            ->dehydrateStateUsing(fn($state) => self::parseRangeString($state))
            ->formatStateUsing(fn($state) => self::rangeToString($state));
    }

    protected static function parseRangeString(?string $value): ?array
    {
        if (!$value) return null;
        $value = str_replace(',', '.', trim($value));

        if (preg_match('/^(\d+(?:\.\d+)?)[\s-]+(\d+(?:\.\d+)?)/', $value, $m)) {
            return ['min' => (float)$m[1], 'max' => (float)$m[2]];
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\+$/', $value, $m)) {
            return ['min' => (float)$m[1], 'max' => null];
        }

        return ['min' => (float)$value, 'max' => (float)$value];
    }

    protected static function rangeToString($state): ?string
    {
        if (!is_array($state)) return $state;
        if (isset($state['min'], $state['max'])) {
            if ($state['max'] === null) return $state['min'] . '+';
            if ($state['min'] === $state['max']) return (string)$state['min'];
            return "{$state['min']}-{$state['max']}";
        }
        return null;
    }

}
