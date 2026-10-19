<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentistCommission extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    public const STATUSES = ['pending', 'paid'];

    protected $fillable = [
        'tenant_id',
        'user_id',
        'treatment_plan_item_id',
        'commission_type', // percentage, fixed
        'commission_value',
        'calculated_amount',
        'status',
        'period', // ej: 2026-08
    ];

    protected $casts = [
        'commission_value'   => 'decimal:2',
        'calculated_amount'  => 'decimal:2',
    ];

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function treatmentPlanItem(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlanItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
