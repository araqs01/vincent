<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TasteResource\Pages;
use App\Models\Taste;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TasteResource extends Resource
{
    protected static ?string $model = Taste::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.taste.plural');
    }

    public static function getLabel(): string
    {
        return __('app.taste.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.taste.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('taste_group_id')
                ->relationship('group', 'name')
                ->label('Группа вкуса'),
            TranslatableContainer::make(
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required(),
            )
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
            Tables\Columns\TextColumn::make('group.name')->label('Группа'),
        ])
            ->filters([
                SelectFilter::make('taste_group_id')
                    ->label('Группа вкуса')
                    ->relationship('group', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

