<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;
use Filament\Tables\Columns\TextColumn;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.collection.plural');
    }

    public static function getLabel(): string
    {
        return __('app.collection.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.collection.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.collection.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.collection.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('app.collection.fields.slug'))
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255),

                    TranslatableContainer::make(
                        Forms\Components\Textarea::make('description')
                            ->label(__('app.collection.fields.description'))
                            ->rows(3),
                    ),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('app.collection.sections.settings'))
                ->schema([
                    Forms\Components\Toggle::make('is_auto')
                        ->label(__('app.collection.fields.is_auto'))
                        ->hint('Если включено — товары будут подбираться по фильтру автоматически.'),

                    Forms\Components\KeyValue::make('filter_formula')
                        ->label(__('app.collection.fields.filter_formula'))
                        ->keyLabel('Поле')
                        ->valueLabel('Значение')
                        ->addButtonLabel('Добавить фильтр')
                        ->reorderable()
                        ->nullable(),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.collection.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('app.collection.fields.slug')),
                Tables\Columns\IconColumn::make('is_auto')
                    ->boolean()
                    ->label(__('app.collection.fields.is_auto')),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Товаров'),
            ])
            ->defaultSort('id', 'desc');
    }


    public static function getRelations(): array
    {
        return [
            // Здесь позже добавим RelationManagers для продуктов
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
