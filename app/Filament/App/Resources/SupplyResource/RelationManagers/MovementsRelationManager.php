<?php

namespace App\Filament\App\Resources\SupplyResource\RelationManagers;

use Closure;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Kardex del insumo. Es un registro de solo adición: los movimientos no
 * se editan ni se eliminan (el modelo ajusta el stock al CREARLOS y no
 * sabría revertirlo). Un error se corrige con un movimiento de ajuste.
 */
class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Movimientos (kardex)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('movement_type')
                ->label('Tipo')
                ->options([
                    'out'        => 'Salida (consumo)',
                    'in'         => 'Entrada sin lote',
                    'adjustment' => 'Ajuste (+ / −)',
                ])
                ->required()
                ->default('out')
                ->live()
                ->helperText('Las entradas con lote se registran en la pestaña Lotes.'),

            Select::make('batch_id')
                ->label('Lote')
                ->options(fn () => $this->getOwnerRecord()
                    ->batches()
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('expiration_date')
                    ->get()
                    ->mapWithKeys(fn ($batch) => [
                        $batch->id => ($batch->batch_number ?: 'Lote #'.$batch->id)
                            .' — quedan '.$batch->quantity_remaining
                            .($batch->expiration_date ? ' — vence '.$batch->expiration_date->format('d/m/Y') : ''),
                    ]))
                ->helperText('Descuenta del lote elegido (conviene usar primero el que vence antes).')
                ->visible(fn (Get $get) => $get('movement_type') === 'out'),

            TextInput::make('quantity')
                ->label('Cantidad')
                ->numeric()
                ->required()
                ->helperText('En un ajuste usa un número negativo para restar.')
                ->rule(function (Get $get) {
                    return function (string $attribute, $value, Closure $fail) use ($get) {
                        $type = $get('movement_type');
                        $quantity = (float) $value;
                        $supply = $this->getOwnerRecord();

                        if ($type === 'adjustment') {
                            if ($quantity == 0.0) {
                                $fail('El ajuste no puede ser 0.');
                            }

                            return;
                        }

                        if ($quantity <= 0) {
                            $fail('La cantidad debe ser mayor a 0.');

                            return;
                        }

                        if ($type === 'out') {
                            if ($quantity > (float) $supply->current_stock) {
                                $fail('La salida supera el stock disponible ('.$supply->current_stock.').');

                                return;
                            }

                            if ($batchId = $get('batch_id')) {
                                $remaining = (float) $supply->batches()->whereKey($batchId)->value('quantity_remaining');

                                if ($quantity > $remaining) {
                                    $fail('El lote solo tiene '.$remaining.' disponibles.');
                                }
                            }
                        }
                    };
                }),

            Textarea::make('notes')
                ->label('Notas')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('movement_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'in'         => 'Entrada',
                        'out'        => 'Salida',
                        'adjustment' => 'Ajuste',
                        default      => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in'         => 'success',
                        'out'        => 'danger',
                        'adjustment' => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('quantity')->label('Cantidad'),

                TextColumn::make('batch.batch_number')
                    ->label('Lote')
                    ->placeholder('—'),

                TextColumn::make('createdBy.name')->label('Registrado por'),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(40),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar movimiento')
                    ->mutateDataUsing(function (array $data): array {
                        $data['created_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
