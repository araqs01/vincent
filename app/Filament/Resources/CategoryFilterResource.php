<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryFilterResource\Pages;
use App\Filament\Resources\CategoryFilterResource\RelationManagers\CategoryFilterOptionsRelationManager;
use App\Models\Category;
use App\Models\CategoryFilter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CategoryFilterResource extends Resource
{
    protected static ?string $model = CategoryFilter::class;
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.category_filter.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->label(__('app.category_filter.fields.category'))
                ->options(Category::orderBy('id')->get()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('key')
                ->label(__('app.category_filter.fields.key'))
                ->placeholder('e.g. color, sugar, grape_type')
                ->unique(ignoreRecord: true)
                ->required(),

            TranslatableContainer::make(
                Forms\Components\TextInput::make('title')
                    ->label(__('app.category_filter.fields.title'))
                    ->required()
                    ->maxLength(255)
            ),

            Forms\Components\Select::make('mode')
                ->label(__('app.category_filter.fields.mode'))
                ->options([
                    'discrete' => 'Discrete (options)',
                    'reference' => 'Reference (external model)',
                    'range' => 'Range (min/max)',
                    'boolean' => 'Boolean (yes/no)',
                    'attribute' => 'Attribute (product field)',
                ])
                ->default('discrete')
                ->required(),

            Forms\Components\TextInput::make('source_model')
                ->label(__('app.category_filter.fields.source_model'))
                ->placeholder('App\\Models\\'),

            Forms\Components\KeyValue::make('config')
                ->label(__('app.category_filter.fields.config'))
                ->keyLabel('Param')
                ->valueLabel('Value'),

            Forms\Components\TextInput::make('order_index')
                ->label(__('app.common.order_index'))
                ->numeric()
                ->default(1),

            Forms\Components\Toggle::make('is_active')
                ->label(__('app.common.is_active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('app.category_filter.fields.category'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('app.category_filter.fields.title'))
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('mode')
                    ->label(__('app.category_filter.fields.mode'))
                    ->colors([
                        'info' => 'discrete',
                        'warning' => 'reference',
                        'success' => 'range',
                        'gray' => 'boolean',
                    ]),

                Tables\Columns\TextColumn::make('key')->label('Key'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label(__('app.common.is_active')),
            ])
            ->defaultSort('category_id')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('app.category_filter.fields.category'))
                    ->options(Category::orderBy('id')->get()->pluck('name', 'id')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CategoryFilterOptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryFilters::route('/'),
            'create' => Pages\CreateCategoryFilter::route('/create'),
            'edit' => Pages\EditCategoryFilter::route('/{record}/edit'),
        ];
    }
}

