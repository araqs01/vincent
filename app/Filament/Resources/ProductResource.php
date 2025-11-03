<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\AttributeValuesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CollectionsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\DishesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\GrapeVariantsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\PairingsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ProductVariantsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\TastesRelationManager;
use App\Imports\ProductImporter;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use FilamentTiptapEditor\TiptapEditor;
use Maatwebsite\Excel\Facades\Excel;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.product.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // 🧩 Основная информация
            Forms\Components\Section::make(__('app.product.sections.main'))
                ->schema([
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.product.fields.name'))
                            ->required()
                            ->maxLength(255),
                    ),

                    TranslatableContainer::make(
                        TiptapEditor::make('description')->label(__('app.product.fields.description'))->required(),
                    ),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('app.product.fields.slug'))
                        ->disabled()
                        ->maxLength(255),
                ])
                ->columns(2),

            // 🗂️ Категории и связи
            Forms\Components\Section::make(__('app.product.sections.classification'))
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label(__('app.product.fields.category'))
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('region_id')
                        ->label(__('app.product.fields.region'))
                        ->relationship('region', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('supplier_id')
                        ->label(__('app.product.fields.supplier'))
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload(),
                ])
                ->columns(3)
                ->collapsible(),

            // 💰 Цены и статус
            Forms\Components\Section::make(__('app.product.sections.pricing'))
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label(__('app.product.fields.price'))
                        ->numeric()
                        ->suffix('₽')
                        ->required(),

                    Forms\Components\TextInput::make('final_price')
                        ->label(__('app.product.fields.final_price'))
                        ->numeric()
                        ->suffix('₽')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label(__('app.product.fields.status'))
                        ->options([
                            'draft' => 'Черновик',
                            'active' => 'Активен',
                            'archived' => 'Архивирован',
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\TextInput::make('rating')
                        ->label(__('app.product.fields.rating'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(5)
                        ->step(0.1),
                ])
                ->columns(4)
                ->collapsible(),

            // 🖼️ Галерея (Spatie)
            Forms\Components\Section::make(__('app.product.sections.media'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('images')
                        ->label(__('app.product.fields.images'))
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->image(),
                ])
                ->collapsible(),
            // ⚙️ Мета и прочее
            Forms\Components\ViewField::make('meta')
                ->label('Мета данные')
                ->view('filament.resources.product.partials.meta-display')

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->headerActions([
                Action::make('import')
                    ->label('Импорт товаров из Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel файл')
                            ->required()
                            ->storeFiles(false)
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                    ])
                    ->action(function (array $data) {
                        try {
                            Excel::import(new ProductImporter, $data['file']->getRealPath());
                            Notification::make()->title('Импорт завершён успешно ✅')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Ошибка импорта')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.product.fields.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('app.product.fields.category')),
                Tables\Columns\TextColumn::make('region.name')
                    ->label(__('app.product.fields.region')),
                Tables\Columns\TextColumn::make('final_price')
                    ->label(__('app.product.fields.final_price'))
                    ->money('RUB', true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'active',
                        'gray' => 'archived',
                    ])
                    ->label(__('app.product.fields.status')),
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
            AttributeValuesRelationManager::class,
//            CollectionsRelationManager::class,
            TastesRelationManager::class,
            GrapeVariantsRelationManager::class,
            PairingsRelationManager::class,
            ProductVariantsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
