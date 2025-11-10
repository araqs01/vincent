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

            'whisky' => Tab::make('Виски / Крепкие напитки')
                ->icon('heroicon-o-fire')
                ->badge(WhiskyTaste::count())
                ->modifyQueryUsing(fn() => WhiskyTaste::query()),

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
            'whisky' => WhiskyTaste::query(),
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

                TextColumn::make('weight')
                    ->label('Вес')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                $this->getCreateGroupAction(),
                $this->getCreateWhiskyAction(),
                $this->getCreateBeerAction(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать')
                    ->form(function () {
                        return match ($this->activeTab) {
                            'groups' => [
                               TranslatableContainer::make(
                                   TextInput::make('name')
                                        ->label('Название группы')
                                        ->required(),
                                ),
                                TranslatableContainer::make(
                                    TextInput::make('type')
                                    ->label('Тип напитка'),
                                )
                            ],
                            'whisky' => [
                                TranslatableContainer::make(
                                    \Filament\Forms\Components\TextInput::make('name')->label('Название')->required(),
                                ),
                                \Filament\Forms\Components\Select::make('group_id')
                                    ->label('Группа вкуса')
                                    ->relationship('groupRelation', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                TranslatableContainer::make(
                                \Filament\Forms\Components\TextInput::make('type')->label('Тип напитка'),
                                ),
                                \Filament\Forms\Components\TextInput::make('weight')->label('Вес')->numeric(),
                            ],
                            'beer' => [
                              TranslatableContainer::make(
                                    \Filament\Forms\Components\TextInput::make('name')->label('Название')->required(),
                                ),
                            ],
                            default => [],
                        };
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('id', 'asc');
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

    protected function getCreateWhiskyAction(): CreateAction
    {
        return CreateAction::make('createWhisky')
            ->visible(fn() => $this->activeTab === 'whisky')
            ->model(WhiskyTaste::class)
            ->label('Создать вкус виски / крепких напитков')
            ->form([
                TextInput::make('name')->label('Название')->required(),
                Select::make('group_id')
                    ->label('Группа вкуса')
                    ->relationship('groupRelation', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                TextInput::make('type')->label('Тип напитка'),
                TextInput::make('weight')->label('Вес')->numeric(),
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
}
