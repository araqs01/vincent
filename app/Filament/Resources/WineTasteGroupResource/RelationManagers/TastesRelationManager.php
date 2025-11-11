<?php

namespace App\Filament\Resources\WineTasteGroupResource\RelationManagers;

use Filament\Forms\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TastesRelationManager extends RelationManager
{
    protected static string $relationship = 'tastes';
    protected static ?string $recordTitleAttribute = 'name';

    public  function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TranslatableContainer::make(
            Forms\Components\TextInput::make('name')
                ->label('Название вкуса')
                ->required(),
            ),
            Section::make('Meta')
                ->schema([
                    Forms\Components\TextInput::make('meta.group_1')
                        ->label('Группа 1 (RU)'),
                    Forms\Components\TextInput::make('meta.group_2')
                        ->label('Группа 2 (RU)'),
                    Forms\Components\TextInput::make('meta.type')
                        ->label('Тип напитка (RU)'),
                    Forms\Components\TextInput::make('meta.type_en')
                        ->label('Тип напитка (EN)'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public  function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name')->label('Название'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('id');
    }
}
