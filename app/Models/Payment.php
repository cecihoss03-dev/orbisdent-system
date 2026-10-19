<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Billing\InvoiceBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    public const STATUSES = ['completed', 'refunded', 'voided'];

    public const METHODS = [
        'cash'     => 'Efectivo',
        'card'     => 'Tarjeta',
        'transfer' => 'Transferencia',
    ];

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'invoice_id',
        'treatment_plan_id',
        'amount',
        'payment_method', // cash, card, transfer
        'payment_date',
        'reference_number',
        'received_by',
        'status',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function booted(): void
    {
        // Mantiene el estado de la factura sincronizado con sus pagos.
        $sync = fn (Payment $p) => InvoiceBalance::sync($p->invoice()->withoutGlobalScopes()->first());

        static::saved($sync);
        static::deleted($sync);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
