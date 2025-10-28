<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Catalog';

    public static function getLabel(): string
    {
        return __('app.region.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('app.region.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.region.singular'))
                ->description(__('app.region.descriptions.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.region.fields.name'))
                            ->required()
                            ->maxLength(255)
                    ),

                    TranslatableContainer::make(
                        Forms\Components\Textarea::make('description')
                            ->label(__('app.region.fields.description'))
                            ->rows(3)
                    ),
                ])
                ->columns(2)
                ->collapsible(),

            // 🔹 Иерархия и иконки
            Forms\Components\Section::make(__('app.region.descriptions.technical'))
                ->schema([
                    Forms\Components\Select::make('parent_id')
                        ->label(__('app.region.fields.parent'))
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->nullable()
                        ->preload()
                        ->hint(__('app.region.hints.parent')),
                ])
                ->columns(3)
                ->collapsible(),
            Forms\Components\Section::make(__('app.region.descriptions.icons'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('icon_terroir')
                        ->collection('icon_terroir')
                        ->label(__('app.region.fields.icon_terroir'))
                        ->image()
                        ->maxFiles(1)
                        ->hint(__('app.region.hints.icon_terroir')),

                    SpatieMediaLibraryFileUpload::make('icon_production')
                        ->collection('icon_production')
                        ->label(__('app.region.fields.icon_production'))
                        ->image()
                        ->maxFiles(1)
                        ->hint(__('app.region.hints.icon_production')),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon_terroir')
                    ->label(__('app.region.fields.icon_terroir'))
                    ->height(40)
                    ->circular(),

                Tables\Columns\ImageColumn::make('icon_production')
                    ->label(__('app.region.fields.icon_production'))
                    ->height(40)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.region.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('app.region.fields.parent'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent')
                    ->relationship('parent', 'name')
                    ->label(__('app.region.fields.parent')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRegions::route('/'),
            'create' => Pages\CreateRegion::route('/create'),
            'edit'   => Pages\EditRegion::route('/{record}/edit'),
        ];
    }
}
