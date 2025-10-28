<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TasteGroupResource\Pages;
use App\Models\TasteGroup;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TasteGroupResource extends Resource
{
    protected static ?string $model = TasteGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $label = 'Группа вкусов';
    protected static ?string $pluralLabel = 'Группы вкусов';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.taste_group.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.taste_group.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),

                    TranslatableContainer::make(
                        Forms\Components\Textarea::make('description')
                            ->label(__('app.taste_group.fields.description'))
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
                    ->label(__('app.taste_group.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tastes_count')
                    ->counts('tastes')
                    ->label('Вкусов'),
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
        return [
            // Позже добавим RelationManager для Tastes
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasteGroups::route('/'),
            'create' => Pages\CreateTasteGroup::route('/create'),
            'edit' => Pages\EditTasteGroup::route('/{record}/edit'),
        ];
    }
}
