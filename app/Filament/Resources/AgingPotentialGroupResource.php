<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgingPotentialGroupResource\Pages;
use App\Models\AgingPotentialGroup;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class AgingPotentialGroupResource extends Resource
{
    protected static ?string $model = AgingPotentialGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $label = 'Группа выдержки';
    protected static ?string $pluralLabel = 'Группы выдержки';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('app.aging.sections.main'))
                ->schema([
                    Forms\Components\TextInput::make('donor_range')
                        ->label(__('app.aging.fields.donor_range'))
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('internal_range')
                        ->label(__('app.aging.fields.internal_range'))
                        ->maxLength(255)
                        ->required(),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('donor_range')
                    ->label(__('app.aging.fields.donor_range'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('internal_range')
                    ->label(__('app.aging.fields.internal_range')),
            ])
            ->defaultSort('id', 'asc')
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
            'index' => Pages\ListAgingPotentialGroups::route('/'),
            'create' => Pages\CreateAgingPotentialGroup::route('/create'),
            'edit' => Pages\EditAgingPotentialGroup::route('/{record}/edit'),
        ];
    }
}
