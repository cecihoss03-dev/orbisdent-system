<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    public const STATUSES = ['issued', 'paid', 'partially_paid', 'void'];

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'treatment_plan_id',
        'invoice_number',
        'issue_date',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
