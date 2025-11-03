<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class MenuBannersRelationManager extends RelationManager
{
    protected static string $relationship = 'menuBanners';
    protected static ?string $title = 'Баннеры меню';
    protected static ?string $icon = 'heroicon-o-photo';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Информация о баннере')
                    ->schema([
                        TranslatableContainer::make(
                            Forms\Components\TextInput::make('title')
                                ->label('Заголовок')
                                ->required(),
                        ),

                        TextInput::make('filter_key')
                            ->label('Ключ фильтра')
                            ->placeholder('Например: rating, top1000, gift')
                            ->helperText('Совпадает с ключом фильтра из JSON (например "rating" или "has_discount")')
                            ->required(),

                        FileUpload::make('image')
                            ->label('Изображение баннера')
                            ->image()
                            ->directory('menu_banners')
                            ->required(),

                        TextInput::make('order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media')
                    ->label('Изображение')
                    ->getStateUsing(fn($record) => $record->getFirstMediaUrl()),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable(),

                Tables\Columns\TextColumn::make('filter_key')
                    ->label('Ключ фильтра')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('order')->label('Порядок')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Активен')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Добавить баннер'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
