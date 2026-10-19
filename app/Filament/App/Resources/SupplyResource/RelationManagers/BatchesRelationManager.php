<?php

namespace App\Filament\App\Resources\SupplyResource\RelationManagers;

use App\Models\Supplier;
use App\Models\SupplyMovement;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Lotes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('supplier_id')
                ->label('Proveedor')
                ->options(fn () => Supplier::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable(),

            TextInput::make('batch_number')
                ->label('N.º de lote')
                ->maxLength(60),

            DatePicker::make('expiration_date')
                ->label('Vencimiento')
                ->native(false)
                ->required(fn () => (bool) $this->getOwnerRecord()->tracks_expiration),

            TextInput::make('quantity_received')
                ->label('Cantidad recibida')
                ->numeric()
                ->required()
                ->minValue(0.01)
                ->disabledOn('edit'),

            TextInput::make('quantity_remaining')
                ->label('Cantidad restante')
                ->numeric()
                ->visibleOn('edit')
                ->disabled()
                ->dehydrated(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_number')
            ->columns([
                TextColumn::make('batch_number')
                    ->label('Lote')
                    ->placeholder('Sin número')
                    ->searchable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor'),

                TextColumn::make('expiration_date')
                    ->label('Vence')
                    ->date()
                    ->sortable()
                    ->color(fn ($record): string => match (true) {
                        $record->expiration_date === null                   => 'gray',
                        $record->expiration_date->isPast()                  => 'danger',
                        $record->expiration_date->lte(now()->addDays(30))   => 'warning',
                        default                                             => 'success',
                    }),

                TextColumn::make('quantity_received')->label('Recibido'),

                TextColumn::make('quantity_remaining')->label('Restante'),

                TextColumn::make('received_at')
                    ->label('Recibido el')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('expiring_soon')
                    ->label('Vencen en 30 días')
                    ->query(fn (Builder $query) => $query->expiringSoon(30))
                    ->toggle(),
            ])
            ->headerActions([
                // Recibir un lote también registra la entrada en el kardex,
                // en la misma transacción, para que stock y lote nunca
                // queden desincronizados.
                CreateAction::make()
                    ->label('Registrar lote')
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data) {
                            $supply = $this->getOwnerRecord();

                            $batch = $supply->batches()->create([
                                ...$data,
                                'quantity_remaining' => $data['quantity_received'],
                            ]);

                            SupplyMovement::create([
                                'supply_id'     => $supply->id,
                                'batch_id'      => $batch->id,
                                'movement_type' => 'in',
                                'quantity'      => $data['quantity_received'],
                                'notes'         => 'Recepción de lote',
                                'created_by'    => Auth::id(),
                            ]);

                            return $batch;
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('expiration_date');
    }
}
