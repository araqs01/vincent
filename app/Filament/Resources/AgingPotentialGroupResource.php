<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgingPotentialGroupResource\Pages;
use App\Models\AgingPotentialGroup;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class AgingPotentialGroupResource extends Resource
{
    protected static ?string $model = AgingPotentialGroup::class;

    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Потенциал выдержки';
    protected static ?string $pluralModelLabel = 'Потенциалы выдержки';
    protected static ?string $modelLabel = 'Потенциал выдержки';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('donor_potential')
                    ->label('Потенциал у донора')
                    ->placeholder('например: 1-2')
                    ->required(),

                Forms\Components\TextInput::make('our_potential')
                    ->label('Наш потенциал')
                    ->placeholder('например: 3-5')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('donor_potential')
                    ->label('Потенциал у донора')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('our_potential')
                    ->label('Наш потенциал')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('group_number')
            ->paginated([10, 25, 50, 100])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgingPotentialGroups::route('/'),
            'create' => Pages\CreateAgingPotentialGroup::route('/create'),
            'edit' => Pages\EditAgingPotentialGroup::route('/{record}/edit'),
        ];
    }
}
