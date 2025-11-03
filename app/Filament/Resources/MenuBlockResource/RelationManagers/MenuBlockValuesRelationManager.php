<?php

namespace App\Filament\Resources\MenuBlockResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class MenuBlockValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';
    protected static ?string $title = 'Values';

    public function form(Form $form): Form
    {
        return $form->schema([
            TranslatableContainer::make(
                Forms\Components\TextInput::make('value')
                    ->label(__('app.menu_block_value.fields.value'))
                    ->required()
                    ->maxLength(255)
            ),

            Forms\Components\TextInput::make('order_index')
                ->label(__('app.common.order_index'))
                ->numeric()
                ->default(1),

            Forms\Components\Toggle::make('is_active')
                ->label(__('app.common.is_active'))
                ->default(true),
        ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value->ru')
            ->columns([
                Tables\Columns\TextColumn::make('value')->label(__('app.menu_block_value.fields.value'))->searchable(),
                Tables\Columns\TextColumn::make('order_index')->label(__('app.menu_block_value.fields.order_index'))->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('app.menu_block_value.fields.is_active'))->boolean()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('reorderUp')
                    ->label('↑ Move Up')
                    ->action(fn ($record) => $record->update(['order_index' => max(1, ($record->order_index ?? 1) - 1)]))
                    ->visible(fn () => true),
                Tables\Actions\Action::make('reorderDown')
                    ->label('↓ Move Down')
                    ->action(fn ($record) => $record->update(['order_index' => ($record->order_index ?? 1) + 1]))
                    ->visible(fn () => true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('order_index', 'asc');
    }
}
