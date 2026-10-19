<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SupplyResource\Pages;
use App\Filament\App\Resources\SupplyResource\RelationManagers;
use App\Models\Supply;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplyResource extends Resource
{
    protected static ?string $model = Supply::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $modelLabel = 'insumo';

    protected static ?string $pluralModelLabel = 'insumos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(150)
                ->columnSpanFull(),

            Select::make('category_id')
                ->label('Categoría')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->label('Nombre de la categoría')
                        ->required()
                        ->maxLength(100),
                ]),

            Select::make('unit_measure')
                ->label('Unidad de medida')
                ->options([
                    'unidad' => 'Unidad',
                    'caja'   => 'Caja',
                    'frasco' => 'Frasco',
                    'par'    => 'Par',
                    'ml'     => 'Mililitros (ml)',
                    'gr'     => 'Gramos (gr)',
                    'kg'     => 'Kilogramos (kg)',
                ])
                ->required(),

            TextInput::make('min_stock')
                ->label('Stock mínimo')
                ->numeric()
                ->required()
                ->default(0)
                ->helperText('Por debajo de este nivel el insumo se marca en rojo.'),

            TextInput::make('current_stock')
                ->label('Stock actual')
                ->numeric()
                ->required()
                ->default(0)
                ->disabledOn('edit')
                ->helperText('Se actualiza solo con lotes y movimientos (pestañas de abajo). Aquí únicamente se fija el stock inicial al crear.'),

            TextInput::make('unit_cost')
                ->label('Costo unitario')
                ->numeric()
                ->prefix('$'),

            Toggle::make('tracks_expiration')
                ->label('Controlar vencimiento')
                ->default(false)
                ->helperText('Si lo activas, cada lote debe tener fecha de vencimiento.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),

                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (Supply $record): string => (float) $record->current_stock <= (float) $record->min_stock ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('min_stock')
                    ->label('Mínimo'),

                TextColumn::make('unit_measure')
                    ->label('Unidad'),

                TextColumn::make('unit_cost')
                    ->label('Costo unit.')
                    ->money('usd')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('tracks_expiration')
                    ->label('Vencimiento')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),

                Filter::make('low_stock')
                    ->label('Bajo stock mínimo')
                    ->query(fn (Builder $query) => $query->lowStock())
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BatchesRelationManager::class,
            RelationManagers\MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSupplies::route('/'),
            'create' => Pages\CreateSupply::route('/create'),
            'edit'   => Pages\EditSupply::route('/{record}/edit'),
        ];
    }
}
