<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.attribute.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('app.attribute.singular'))
                    ->description(__('app.attribute.descriptions.main'))
                    ->schema([
                        TranslatableContainer::make(
                            Forms\Components\TextInput::make('name')
                                ->label(__('app.attribute.fields.name'))
                                ->required()
                                ->maxLength(255)
                        ),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('app.attribute.fields.slug'))
                            ->disabled()
                            ->hint('Используется для импорта и API'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make(__('app.attribute.descriptions.visibility'))
                    ->schema([
                        Forms\Components\Select::make('data_type')
                            ->label(__('app.attribute.fields.data_type'))
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'boolean' => 'Boolean',
                                'select' => 'Select',
                                'multiselect' => 'Multiselect',
                                'range' => 'Range',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable(),

                        TranslatableContainer::make(
                            Forms\Components\TextInput::make('unit')
                                ->label(__('app.attribute.fields.unit'))
                                ->placeholder('Например: л, %, °C')
                        ),

                        Forms\Components\Toggle::make('is_filterable')
                            ->label(__('app.attribute.fields.is_filterable'))
                            ->default(true),

                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('app.attribute.fields.is_visible'))
                            ->default(true),
                    ])
                    ->columns(4)
                    ->collapsible(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.attribute.fields.name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('app.attribute.fields.slug'))
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('data_type')
                    ->label(__('app.attribute.fields.data_type'))
                    ->colors([
                        'primary' => 'string',
                        'success' => 'integer',
                        'warning' => 'select',
                        'danger' => 'multiselect',
                        'info' => 'range',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('app.attribute.fields.is_filterable'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('app.attribute.fields.is_visible'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('data_type')
                    ->label(__('app.attribute.fields.data_type'))
                    ->options([
                        'string' => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'select' => 'Select',
                        'multiselect' => 'Multiselect',
                        'range' => 'Range',
                    ]),
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
            'index'  => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit'   => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
