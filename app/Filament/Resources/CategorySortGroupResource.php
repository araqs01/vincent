<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategorySortGroupResource\Pages;
use App\Models\Category;
use App\Models\CategorySortGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Filters\SelectFilter;

class CategorySortGroupResource extends Resource
{
    protected static ?string $model = CategorySortGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.category_sort_group.plural');
    }

    public static function getLabel(): string
    {
        return __('app.category_sort_group.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.category_sort_group.plural');
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Select::make('category_id')
                ->label('Категория')
                ->relationship('category', 'name')
                ->searchable()
                ->required(),

            TextInput::make('key')
                ->label('Ключ')
                ->required(),

            TextInput::make('title.ru')
                ->label('Название (RU)')
                ->required(),

            TextInput::make('title.en')
                ->label('Название (EN)')
                ->required(),

            Select::make('ui_type')
                ->label('Тип UI')
                ->options([
                    'dropdown' => 'Dropdown',
                    'scale' => 'Scale',
                    'toggle' => 'Toggle',
                ])
                ->default('dropdown'),

            Toggle::make('is_active')
                ->label('Активна')
                ->default(true),

            TextInput::make('order_index')
                ->label('Порядок')
                ->numeric()
                ->default(1),

            Repeater::make('options')
                ->relationship('options')
                ->label('Опции сортировки')
                ->schema([
                    TextInput::make('key')->label('Ключ')->required(),
                    TextInput::make('title.ru')->label('RU')->required(),
                    TextInput::make('title.en')->label('EN')->required(),
                    TextInput::make('field')->label('Поле')->placeholder('например: price'),
                    Select::make('direction')->label('Направление')->options([
                        'asc' => 'Возрастание',
                        'desc' => 'Убывание',
                    ])->default('asc'),
                    Select::make('type')->label('Тип')->options([
                        'scale' => 'Scale',
                        'boolean' => 'Boolean',
                        'custom' => 'Custom',
                    ])->default('scale'),
                    Select::make('ui_type')->label('UI тип')->options([
                        'dropdown' => 'Dropdown',
                        'scale' => 'Scale',
                        'toggle' => 'Toggle',
                    ])->default('dropdown'),
                    KeyValue::make('meta')
                        ->label('Meta данные (JSON)')
                        ->addButtonLabel('Добавить')
                        ->keyLabel('Ключ')
                        ->valueLabel('Значение'),
                    Toggle::make('is_active')->label('Активна')->default(true),
                    TextInput::make('order_index')->numeric()->default(1),
                ])
                ->orderable('order_index')
                ->collapsed()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ui_type')
                    ->label('UI'),

                Tables\Columns\TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Опций'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активна'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(Category::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->placeholder('Все категории'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorySortGroups::route('/'),
            'create' => Pages\CreateCategorySortGroup::route('/create'),
            'edit' => Pages\EditCategorySortGroup::route('/{record}/edit'),
        ];
    }
}
