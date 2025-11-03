<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.references');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.supplier.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.supplier.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.supplier.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),

                    Forms\Components\Textarea::make('contact_info')
                        ->label(__('app.supplier.fields.contact_info'))
                        ->rows(3),

                    Forms\Components\TextInput::make('min_order')
                        ->label(__('app.supplier.fields.min_order'))
                        ->numeric()
                        ->suffix('₽'),

                    Forms\Components\TextInput::make('delivery_time')
                        ->label(__('app.supplier.fields.delivery_time'))
                        ->suffix('дн.'),

                    Forms\Components\TextInput::make('rating')
                        ->label(__('app.supplier.fields.rating'))
                        ->numeric()
                        ->step(0.1)
                        ->minValue(0)
                        ->maxValue(5),
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
                    ->label(__('app.supplier.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_order')
                    ->label(__('app.supplier.fields.min_order')),
                Tables\Columns\TextColumn::make('delivery_time')
                    ->label(__('app.supplier.fields.delivery_time')),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('app.supplier.fields.rating')),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Товаров'),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
