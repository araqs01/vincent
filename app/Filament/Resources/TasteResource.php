<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TasteResource\Pages;
use App\Models\Taste;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TasteResource extends Resource
{
    protected static ?string $model = Taste::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $label = 'Вкус';
    protected static ?string $pluralLabel = 'Вкусы';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.taste.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.taste.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),

                    Forms\Components\Select::make('taste_group_id')
                        ->label(__('app.taste.fields.group'))
                        ->relationship('group', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('weight')
                        ->label(__('app.taste.fields.weight'))
                        ->numeric()
                        ->default(0),
                ])
                ->columns(3)
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.taste.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('app.taste.fields.group'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label(__('app.taste.fields.weight')),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Товаров'),
            ])
            ->defaultSort('weight', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTastes::route('/'),
            'create' => Pages\CreateTaste::route('/create'),
            'edit' => Pages\EditTaste::route('/{record}/edit'),
        ];
    }
}
