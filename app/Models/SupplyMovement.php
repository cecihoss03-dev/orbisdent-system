<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyMovement extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    public const TYPES = ['in', 'out', 'adjustment'];

    protected $fillable = [
        'tenant_id',
        'supply_id',
        'batch_id',
        'movement_type',
        'quantity',
        'related_appointment_id',
        'related_treatment_plan_item_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Cada movimiento ajusta automáticamente el stock del insumo (y
        // el remanente del lote si aplica), sin depender de que el
        // controller/Filament Action recalcule current_stock a mano.
        static::created(function (SupplyMovement $movement) {
            $delta = match ($movement->movement_type) {
                'in'         => $movement->quantity,
                'out'        => -$movement->quantity,
                'adjustment' => $movement->quantity, // ya viene con el signo correcto
                default      => 0,
            };

            $movement->supply()->increment('current_stock', $delta);

            if ($movement->batch_id && $movement->movement_type === 'out') {
                $movement->batch()->decrement('quantity_remaining', $movement->quantity);
            }
        });
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SupplyBatch::class, 'batch_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'related_appointment_id');
    }

    public function treatmentPlanItem(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlanItem::class, 'related_treatment_plan_item_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
