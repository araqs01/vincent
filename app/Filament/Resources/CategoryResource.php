<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Catalog';

    public static function getLabel(): string
    {
        return __('app.category.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.category.plural');
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.category.singular'))
                    ->description(__('app.category.descriptions.main'))
                    ->schema([
                        TranslatableContainer::make(
                            Forms\Components\TextInput::make('name')
                                ->label(__('app.category.fields.name'))
                                ->required()
                                ->maxLength(255),
                        ),

                        TranslatableContainer::make(
                            Forms\Components\Textarea::make('description')
                                ->label(__('app.category.fields.description'))
                                ->rows(3),
                        ),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make(__('app.category.fields.slug'))
                    ->description(__('app.category.descriptions.technical'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label(__('app.category.fields.slug'))
                            ->disabled(),

                        Forms\Components\Select::make('type')
                            ->label(__('app.category.fields.type'))
                            ->options([
                                'wine' => 'Wine',
                                'spirits' => 'Spirits',
                                'beer' => 'Beer',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('parent_id')
                            ->label(__('app.category.fields.parent'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->hint(__('app.category.hints.parent')),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ])
            ->columns(2);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.category.fields.name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('app.category.fields.type'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('app.category.fields.parent'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('app.category.fields.slug'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'wine' => 'Wine',
                        'spirits' => 'Spirits',
                        'beer' => 'Beer',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\CategoryResource\RelationManagers\AttributesRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
