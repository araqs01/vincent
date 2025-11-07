<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PairingGroupResource\Pages;
use App\Models\PairingGroup;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class PairingGroupResource extends Resource
{
    protected static ?string $model = PairingGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Группы сочетаний';
    protected static ?string $pluralModelLabel = 'Группы сочетаний';
    protected static ?string $modelLabel = 'Группа сочетаний';
    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                TranslatableContainer::make(
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
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
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('ID')->toggleable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pairings_count')
                    ->counts('pairings')
                    ->label('Кол-во сочетаний')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPairingGroups::route('/'),
            'create' => Pages\CreatePairingGroup::route('/create'),
            'edit' => Pages\EditPairingGroup::route('/{record}/edit'),
        ];
    }
}
