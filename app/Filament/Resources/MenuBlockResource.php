<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuBlockResource\Pages;
use App\Filament\Resources\MenuBlockResource\RelationManagers\MenuBlockValuesRelationManager;
use App\Models\Category;
use App\Models\MenuBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class MenuBlockResource extends Resource
{
    protected static ?string $model = MenuBlock::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.site_structure');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.menu_block.plural');
    }


//    public static function getNavigationGroup(): ?string
//    {
//        return __('app.catalog');
//    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.menu_block.descriptions.main'))
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label(__('app.menu_block.fields.category'))
                        ->options(fn() => Category::orderBy('id')
                            ->get()
                            ->mapWithKeys(fn($cat) => [
                                $cat->id => $cat->getTranslation('name', 'ru')
                                    ?? $cat->getTranslation('name', 'en')
                                        ?? "Категория #{$cat->id}"
                            ])
                        )
                        ->searchable()
                        ->required(),

                    // 🔥 Translatable title fields
                    TranslatableContainer::make(
                        Forms\Components\TextInput::make('title')
                            ->label(__('app.menu_block.fields.title'))
                            ->required()
                            ->maxLength(255)
                    ),

                    Forms\Components\Select::make('type')
                        ->label(__('app.menu_block.fields.type'))
                        ->native(false)
                        ->searchable()
                        ->required()
                        ->options(function () {
                            $jsonTypes = [];
                            $path = base_path('database/seeders/catalog/categories_from_excel.json');
                            if (File::exists($path)) {
                                $json = json_decode(File::get($path), true);
                                $jsonTypes = collect($json['categories'] ?? [])
                                    ->flatMap(fn ($c) => collect($c['menu_blocks'] ?? [])->pluck('type'))
                                    ->filter()->values()->all();
                            }
                            $dbTypes = MenuBlock::query()->select('type')->distinct()->pluck('type')->filter()->values()->all();
                            $types = collect($jsonTypes)->merge($dbTypes)->unique()->values()->all();
                            return array_combine($types, $types);
                        })
                        // 🛡️ UI-блокировка при редактировании, если у блока уже есть values
                        ->disabled(fn ($record) => $record?->values()->exists())          // или ->values()->count() >= 5 для "много"
                        ->dehydrated(fn ($record) => ! ($record?->values()->exists()))    // чтобы при disabled поле не перетирало БД
                        ->helperText(fn ($record) => $record?->values()->exists()
                            ? __('Нельзя менять тип: у блока уже есть значения. Удалите значения или создайте новый блок.')
                            : __('app.menu_block.hints.type')),

                    Forms\Components\TextInput::make('order_index')
                        ->label(__('app.common.order_index'))
                        ->numeric()
                        ->default(1),

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('app.common.is_active'))
                        ->default(true),
                ])
                ->columns(1),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category_id')
                    ->label(__('app.menu_block.fields.category'))
                    ->formatStateUsing(fn($state) => Category::find($state)?->name ?? '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_index')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn() => Category::orderBy('id')->get()->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('category_id', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            MenuBlockValuesRelationManager::class, // вложенные values
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuBlocks::route('/'),
            'create' => Pages\CreateMenuBlock::route('/create'),
            'edit' => Pages\EditMenuBlock::route('/{record}/edit'),
        ];
    }
}
