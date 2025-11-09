<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PairingResource\Pages;
use App\Models\Pairing;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class PairingResource extends Resource
{
    protected static ?string $model = Pairing::class;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.pairing.plural');
    }

    public static function getLabel(): string
    {
        return __('app.pairing.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.pairing.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TranslatableContainer::make(
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required(),
            ),
            Forms\Components\Section::make(__('app.product.sections.media'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('hero_image')
                        ->label('icon')
                        ->collection('hero_image')
                        ->reorderable()
                        ->image(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPairings::route('/'),
            'create' => Pages\CreatePairing::route('/create'),
            'edit' => Pages\EditPairing::route('/{record}/edit'),
        ];
    }
}
