<?php

namespace App\Filament\Resources\WineTasteGroupResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class TastesRelationManager extends RelationManager
{
    protected static string $relationship = 'tastes';
    protected static ?string $recordTitleAttribute = 'name';

    public  function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Название вкуса')
                ->required(),
            Forms\Components\Textarea::make('meta')
                ->label('Meta')
                ->rows(3),
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
