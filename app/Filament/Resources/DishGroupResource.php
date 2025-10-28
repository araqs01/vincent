<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DishGroupResource\Pages;
use App\Models\DishGroup;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class DishGroupResource extends Resource
{
    protected static ?string $model = DishGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $label = 'Группа блюд';
    protected static ?string $pluralLabel = 'Группы блюд';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.dish_group.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.dish_group.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),
                    TranslatableContainer::make(
                        Forms\Components\Textarea::make('description')
                            ->label(__('app.dish_group.fields.description'))
                            ->rows(3),
                    ),
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
                    ->label(__('app.dish_group.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('dishes_count')
                    ->counts('dishes')
                    ->label('Блюд'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDishGroups::route('/'),
            'create' => Pages\CreateDishGroup::route('/create'),
            'edit' => Pages\EditDishGroup::route('/{record}/edit'),
        ];
    }
}
