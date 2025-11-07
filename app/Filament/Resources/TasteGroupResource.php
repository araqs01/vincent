<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TasteGroupResource\Pages;
use App\Models\TasteGroup;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TasteGroupResource extends Resource
{
    protected static ?string $model = TasteGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.taste_group.plural');
    }

    public static function getLabel(): string
    {
        return __('app.taste_group.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.taste_group.plural');
    }

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
                    Forms\Components\Section::make(__('app.product.sections.media'))
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('images')
                                ->label('icon')
                                ->collection('images')
                                ->reorderable()
                                ->image(),
                        ])
                        ->collapsible(),
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
            // Позже добавим RelationManagers для Tastes
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
