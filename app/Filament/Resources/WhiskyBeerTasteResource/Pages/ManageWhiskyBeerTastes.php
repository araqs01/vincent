<?php

namespace App\Filament\Resources\WhiskyBeerTasteResource\Pages;

use App\Filament\Resources\WhiskyBeerTasteResource;
use App\Models\BeerTaste;
use App\Models\WhiskyTaste;
use App\Models\WhiskyTasteGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Livewire\Attributes\Url;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class ManageWhiskyBeerTastes extends ManageRecords
{
    protected static string $resource = WhiskyBeerTasteResource::class;
    protected static ?string $title = '🥃 Виски – Крепкие напитки – Пиво – вкусы и группы';

    // ✅ Активная вкладка хранится в Livewire state и синхронизируется с URL
    #[Url(as: 'activeTab')]
    public ?string $activeTab = 'groups';

    /** ------------------------------
     * 🧭 ТАБЫ
     * ------------------------------ */
    public function getTabs(): array
    {
        return [
            'groups' => Tab::make('Группы вкусов')
                ->icon('heroicon-o-rectangle-group')
                ->badge(WhiskyTasteGroup::count())
                ->modifyQueryUsing(fn() => WhiskyTasteGroup::query()),

            'beer' => Tab::make('Пиво')
                ->icon('heroicon-o-beaker')
                ->badge(BeerTaste::count())
                ->modifyQueryUsing(fn() => BeerTaste::query()),
        ];
    }

    /** ------------------------------
     * 📊 Таблица
     * ------------------------------ */
    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return match ($this->activeTab) {
            'beer' => BeerTaste::query(),
            default => WhiskyTasteGroup::query(),
        };
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('ID'),

                TextColumn::make('name')
                    ->label('Название (RU)')
                    ->getStateUsing(fn($record) => $record->name)
                    ->searchable(),


                TextColumn::make('type')
                    ->label('Тип напитка')
                    ->getStateUsing(fn($record) => $record->type)
                    ->toggleable(),

                TextColumn::make('tastes_count')
                    ->label('Кол-во вкусов')
                    ->counts('tastes') // Eloquent counts relation
                    ->sortable()
                    ->visible(fn() => $this->activeTab === 'groups'),

                TextColumn::make('weight')
                    ->label('Вес')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                $this->getCreateGroupAction(),
                $this->getCreateBeerAction(),
            ])
            ->actions([
                // 👁 Открыть (только для групп)
                Tables\Actions\ViewAction::make()
                    ->label('Открыть')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => $this->activeTab === 'groups'
                        ? \App\Filament\Resources\WhiskyBeerTasteResource::getUrl('view', ['record' => $record])
                        : null
                    )
                    ->visible(fn() => $this->activeTab === 'groups'),
                // ✏️ Редактировать
                Tables\Actions\EditAction::make()
                    ->label('Редактировать')
                    ->form(function () {
                        return match ($this->activeTab) {
                            'groups' => [
                                \Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer::make(
                                    \Filament\Forms\Components\TextInput::make('name')
                                        ->label('Название группы')
                                        ->required(),
                                ),
                                \Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer::make(
                                    \Filament\Forms\Components\TextInput::make('type')
                                        ->label('Тип напитка'),
                                ),
                            ],
                            'beer' => [
                                \Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer::make(
                                    \Filament\Forms\Components\TextInput::make('name')->label('Название')->required(),
                                ),
                            ],
                            default => [],
                        };
                    }),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /** ------------------------------
     * 🧩 CREATE ACTIONS (разные формы)
     * ------------------------------ */

    protected function getCreateGroupAction(): CreateAction
    {
        return CreateAction::make('createGroup')
            ->visible(fn() => $this->activeTab === 'groups')
            ->model(WhiskyTasteGroup::class)
            ->label('Создать группу вкусов')
            ->form([
                TextInput::make('name')
                    ->label('Название группы')
                    ->required(),
                TextInput::make('type')
                    ->label('Тип напитка'),
            ]);
    }


    protected function getCreateBeerAction(): CreateAction
    {
        return CreateAction::make('createBeer')
            ->visible(fn() => $this->activeTab === 'beer')
            ->model(BeerTaste::class)
            ->label('Создать вкус пива')
            ->form([
                TextInput::make('name')->label('Название')->required(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\WhiskyBeerTasteResource\RelationManagers\TastesRelationManager::class,
        ];
    }
}
