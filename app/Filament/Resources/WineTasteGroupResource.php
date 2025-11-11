<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WineTasteGroupResource\Pages;
use App\Filament\Resources\WineTasteGroupResource\RelationManagers\TastesRelationManager;
use App\Models\WineTasteGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class WineTasteGroupResource extends Resource
{
    protected static ?string $model = WineTasteGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Вино - Шампанское - Вкусы';
    protected static ?string $pluralModelLabel = 'Вино - Шампанское - Вкусы';
    protected static ?string $modelLabel = 'Вино - Шампанское - Вкусы';
    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TranslatableContainer::make(
                Forms\Components\TextInput::make('name')
                    ->label('Название группы')
                    ->required(),
            ),
            TranslatableContainer::make(
                Forms\Components\TextInput::make('type')
                    ->label('Тип напитка (Wine / Champagne / etc.)')
                    ->required(),
            ),
            Forms\Components\Textarea::make('meta')
                ->label('Meta (дополнительные данные)')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип напитка')
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tastes_count')
                    ->counts('tastes')
                    ->label('Количество вкусов')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id');
    }

    public static function getRelations(): array
    {
        return [
          TastesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWineTasteGroups::route('/'),
            'create' => Pages\CreateWineTasteGroup::route('/create'),
            'edit' => Pages\EditWineTasteGroup::route('/{record}/edit'),
        ];
    }
}
