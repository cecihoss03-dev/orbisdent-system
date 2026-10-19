<?php

namespace App\Filament\App\Resources\SupplyResource\Pages;

use App\Filament\App\Resources\SupplyResource;
use App\Models\SupplyMovement;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateSupply extends CreateRecord
{
    protected static string $resource = SupplyResource::class;

    /**
     * El stock inicial no se guarda directo en el insumo: se crea con
     * stock 0 y se registra un movimiento de ajuste "Stock inicial".
     * Así el kardex queda completo desde el primer día y el hook de
     * SupplyMovement deja current_stock en el valor correcto sin que
     * se sume dos veces.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $initialStock = (float) ($data['current_stock'] ?? 0);
        $data['current_stock'] = 0;

        return DB::transaction(function () use ($data, $initialStock) {
            $supply = static::getResource()::getModel()::create($data);

            if ($initialStock > 0) {
                SupplyMovement::create([
                    'supply_id'     => $supply->id,
                    'movement_type' => 'adjustment',
                    'quantity'      => $initialStock,
                    'notes'         => 'Stock inicial',
                    'created_by'    => Auth::id(),
                ]);
            }

            return $supply;
        });
    }
}
