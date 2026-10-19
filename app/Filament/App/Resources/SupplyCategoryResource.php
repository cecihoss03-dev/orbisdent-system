<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SupplyCategoryResource\Pages;
use App\Models\SupplyCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupplyCategoryResource extends Resource
{
    protected static ?string $model = SupplyCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $modelLabel = 'categoría de insumos';

    protected static ?string $pluralModelLabel = 'categorías de insumos';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(100)
                ->helperText('Ej. Anestesia, Materiales de obturación, Bioseguridad.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('supplies_count')->label('Insumos')->counts('supplies'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSupplyCategories::route('/'),
            'create' => Pages\CreateSupplyCategory::route('/create'),
            'edit'   => Pages\EditSupplyCategory::route('/{record}/edit'),
        ];
    }
}
