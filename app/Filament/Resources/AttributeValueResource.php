<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeValueResource\Pages;
use App\Models\AttributeValue;
use App\Models\Attribute;
use App\Models\Product;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Support\Str;

class AttributeValueResource extends Resource
{
    protected static ?string $model = AttributeValue::class;
    protected static ?string $navigationIcon = 'heroicon-o-variable';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.attribute_value.plural');
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.attribute_value.descriptions.main'))
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label(__('app.attribute_value.fields.product'))
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('attribute_id')
                        ->label(__('app.attribute_value.fields.attribute'))
                        ->relationship('attribute', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Textarea::make('value')
                        ->label(__('app.attribute_value.fields.value'))
                        ->rows(3)
                        ->required()
                        ->hint('Хранится в JSON (для multiselect или range значений)'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('products.name')
                    ->label(__('app.attribute_value.fields.product'))
                    ->limit(50) // ограничивает длину строки
                    ->tooltip(fn ($record) => $record->products->pluck('name')->join(', '))
                    ->formatStateUsing(fn ($state) => Str::limit(collect($state)->join(', '), 50))
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('attribute.name')
                    ->label(__('app.attribute_value.fields.attribute'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('app.attribute_value.fields.value'))
                    ->limit(50)
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : $state)
                    ->copyable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('attribute_id')
                    ->label(__('app.attribute_value.fields.attribute'))
                    ->relationship('attribute', 'name'),
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
            'index'  => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'edit'   => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
