<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SommelierGroupResource\Pages;
use App\Models\SommelierGroup;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class SommelierGroupResource extends Resource
{
    protected static ?string $model = SommelierGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $modelLabel = 'Группа сомелье';
    protected static ?string $pluralModelLabel = 'Вино-теги-сомелье';

    public static function form(\Filament\Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TranslatableContainer::make(
                Forms\Components\TextInput::make('name')
                    ->label('Название (RU)')
                    ->required(),
                ),
            ]),
            TextInput::make('slug')
                ->label('Slug')
                ->hint('Создается автоматически при вводе названия')
                ->reactive()
                ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('order_index')
                ->numeric()
                ->label('Порядок')
                ->default(0),
        ]);
    }

    public static function table(\Filament\Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Название')->searchable(),
                TextColumn::make('slug')->label('Slug')->sortable(),
                TextColumn::make('order_index')->label('Порядок'),
                TextColumn::make('tags_count')->counts('tags')->label('Количество тегов'),
            ])
            ->defaultSort('order_index')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SommelierGroupResource\RelationManagers\TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSommelierGroups::route('/'),
            'create' => Pages\CreateSommelierGroup::route('/create'),
            'edit' => Pages\EditSommelierGroup::route('/{record}/edit'),
        ];
    }
}
