<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Attribute;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Атрибут')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('pivot.is_required')
                    ->boolean()
                    ->label('Обязательный'),

                Tables\Columns\TextColumn::make('pivot.order_index')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('attachAttribute')
                    ->label('Прикрепить атрибут')
                    ->icon('heroicon-o-plus')
                    ->button()
                    ->form([
                        Forms\Components\Select::make('attribute_id')
                            ->label('Атрибут')
                            ->options(\App\Models\Attribute::query()
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Выберите атрибут'),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Обязательный')
                            ->default(false),

                        Forms\Components\TextInput::make('order_index')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),
                    ])
                    ->action(function (array $data): void {
                        // ✅ Получаем текущую категорию через RelationManager
                        $category = $this->getOwnerRecord();

                        // Проверим, не прикреплён ли уже этот атрибут
                        if ($category->attributes()->where('attribute_id', $data['attribute_id'])->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Этот атрибут уже прикреплён!')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Создаём связь
                        $category->attributes()->attach($data['attribute_id'], [
                            'is_required' => $data['is_required'],
                            'order_index' => $data['order_index'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Атрибут успешно прикреплён!')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('category_attribute.order_index');
    }
}
