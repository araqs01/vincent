<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhiskyBaseResource\Pages;
use App\Filament\Resources\WhiskyBaseResource\RelationManagers;
use App\Models\WhiskyBase;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;


class WhiskyBaseResource extends Resource
{
    protected static ?string $model = WhiskyBase::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Виски — базовая таблица';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $slug = 'whiskies-base';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TranslatableContainer::make(
                TextInput::make('name')->label('Название виски')->required(),
            ),

            TranslatableContainer::make(
                TextInput::make('manufacturer')->label('Производитель'),
            ),
            TranslatableContainer::make(
                TextInput::make('country')->label('Страна'),
            ),
            Grid::make(3)->schema([
                Toggle::make('is_blended')->label('Купажированный'),
                Toggle::make('for_cigar')->label('Под сигару'),
            ]),

            Grid::make(4)->schema([
                TextInput::make('sweetness')->numeric()->label('Сладость'),
                TextInput::make('smoky')->numeric()->label('Торф / Дым'),
                TextInput::make('fruity')->numeric()->label('Фрукты'),
                TextInput::make('spicy')->numeric()->label('Специи'),
                TextInput::make('floral')->numeric()->label('Цветочный'),
                TextInput::make('woody')->numeric()->label('Дерево'),
                TextInput::make('grainy')->numeric()->label('Зернистость'),
                TextInput::make('creamy')->numeric()->label('Сливочный'),
                TextInput::make('sulphury')->numeric()->label('Сульфаты'),
                TextInput::make('smooth')->numeric()->label('Мягкость'),
                TextInput::make('finish_length')->numeric()->label('Послевкусие (длительность)'),
                TextInput::make('bitterness')->numeric()->label('Горчинка'),
                TextInput::make('dryness')->numeric()->label('Сухость'),
                TextInput::make('body')->numeric()->label('Плотность'),
            ]),

            Grid::make(2)->schema([
                Textarea::make('aroma')->label('Аромат')->rows(2),
                Textarea::make('taste')->label('Вкус')->rows(2),
                Textarea::make('aftertaste')->label('Послевкусие')->rows(2),
            ]),

            Grid::make(2)->schema([
                TextInput::make('blend_included')->label('Входит в купаж (через запятую)'),
                TextInput::make('blend_with')->label('В купаже с (через запятую)'),
            ]),

            Textarea::make('awards')->label('Награды')->rows(2),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('ID')->toggleable(),
                TextColumn::make('name')->label('Название')->searchable()->limit(40),
                TextColumn::make('manufacturer')->label('Производитель')->limit(30)->searchable(),
                TextColumn::make('country')->label('Страна')->limit(20)->sortable(),

                TextColumn::make('sweetness')->label('Сладость')->sortable(),
                TextColumn::make('smoky')->label('Торф/Дым')->sortable(),
                TextColumn::make('fruity')->label('Фрукты')->sortable(),
                TextColumn::make('body')->label('Плотность')->sortable(),

                IconColumn::make('is_blended')
                    ->boolean()
                    ->label('Купаж'),

                IconColumn::make('for_cigar')
                    ->boolean()
                    ->label('Под сигару'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->label('Страна')
                    ->options(
                        WhiskyBase::query()
                            ->select('country->ru as ru')
                            ->distinct()
                            ->pluck('ru', 'ru')
                            ->filter()
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('manufacturer')
                    ->label('Производитель')
                    ->options(
                        WhiskyBase::query()
                            ->select('manufacturer->ru as ru')
                            ->distinct()
                            ->pluck('ru', 'ru')
                            ->filter()
                            ->toArray()
                    ),
                Tables\Filters\Filter::make('sweetness')
                    ->form([
                        TextInput::make('min')->label('Мин. сладость')->numeric(),
                        TextInput::make('max')->label('Макс. сладость')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min'], fn($q, $min) => $q->where('sweetness', '>=', $min))
                            ->when($data['max'], fn($q, $max) => $q->where('sweetness', '<=', $max));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
            ])
            ->defaultSort('id', 'desc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhiskyBases::route('/'),
            'create' => Pages\CreateWhiskyBase::route('/create'),
            'edit' => Pages\EditWhiskyBase::route('/{record}/edit'),
        ];
    }
}
