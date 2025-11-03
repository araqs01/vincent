<?php
//decreate i logika kar vor import aneluc ffilternery shatanum ein

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\MenuBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class MenuBlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'menuBlocks';

  public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
  {
      return __('app.menu_block.plural');
  }

    public function form(Form $form): Form
    {
        return $form->schema([
            TranslatableContainer::make(
                Forms\Components\TextInput::make('title')
                    ->label(__('app.menu_block.fields.title'))
                    ->required()
                    ->maxLength(255),
            ),

            Forms\Components\TextInput::make('type')
                ->label(__('app.menu_block.fields.type'))
                ->required()
                ->maxLength(255),

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
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('app.menu_block.fields.title'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('app.menu_block.fields.type'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_index')
                    ->label(__('app.common.order_index'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('app.common.is_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order_index');
    }
}
