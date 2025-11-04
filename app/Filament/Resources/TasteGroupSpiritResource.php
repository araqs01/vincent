<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TasteGroupSpiritResource\Pages;
use App\Models\TasteGroupSpirit;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class TasteGroupSpiritResource extends Resource
{
    protected static ?string $model = TasteGroupSpirit::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Группы (Крепкие напитки)';
    protected static ?string $modelLabel = 'Группа букетов (Spirit)';
    protected static ?string $pluralModelLabel = 'Группы букетов (Spirit)';

    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation_groups.raw_materials');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Group::make([
                TranslatableContainer::make(
                    TextInput::make('name')
                        ->label('Название')
                        ->required(),
                )->columnSpanFull(),

                FileUpload::make('image')
                    ->label('Изображение')
                    ->image()
                    ->directory('taste_groups_spirit')
                    ->visibility('public')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            ImageColumn::make('image')
                ->label('Изображение')
                ->square()
                ->height(50),

            TextColumn::make('name')
                ->label('Название')
                ->sortable()
                ->searchable(),

            TextColumn::make('description')
                ->label('Описание')
                ->limit(80),
        ])
            ->defaultSort('id', 'desc')
            ->filters([])
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
            'index' => Pages\ListTasteGroupSpirits::route('/'),
            'create' => Pages\CreateTasteGroupSpirit::route('/create'),
            'edit' => Pages\EditTasteGroupSpirit::route('/{record}/edit'),
        ];
    }
}
