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
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.references');
    }

    public static function getNavigationLabel(): string
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
                    SpatieMediaLibraryFileUpload::make('hero_image')
                        ->collection('icon_production')
                        ->label(__('Шапка'))
                        ->image()
                        ->maxFiles(1),

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
            ->modifyQueryUsing(fn($query) => $query->with([
                'parent',
                'parent.parent',
                'parent.parent.parent',
                'parent.parent.parent.parent',
            ]))
            ->columns([
                // 🌲 Уровень
                Tables\Columns\TextColumn::make('level')
                    ->label('Уровень')
                    ->getStateUsing(function ($record) {
                        $level = 1;
                        $parent = $record->parent;
                        while ($parent) {
                            $level++;
                            $parent = $parent->parent;
                        }
                        return $level;
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        1 => 'success',
                        2 => 'info',
                        3 => 'warning',
                        4 => 'danger',
                        default => 'gray',
                    }),

                // 🏷 Название
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->formatStateUsing(function ($record) {
                        $depth = 0;
                        $parent = $record->parent;
                        while ($parent) {
                            $depth++;
                            $parent = $parent->parent;
                        }
                        $indent = str_repeat(' ', $depth * 2);
                        $arrow = $depth > 0 ? '↳ ' : '';
                        return $indent . $arrow . $record->name;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Родительский регион')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
