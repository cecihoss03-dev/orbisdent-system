<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlanItem extends Model
{
    public $timestamps = false;

    public const STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    protected $fillable = [
        'treatment_plan_id',
        'treatment_id',
        'tooth_fdi_code',
        'surface_code',
        'quantity',
        'unit_price',
        'discount',
        'subtotal',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount'   => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentCatalog::class, 'treatment_id');
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(ToothDefinition::class, 'tooth_fdi_code', 'fdi_code');
    }

    public function surface(): BelongsTo
    {
        return $this->belongsTo(ToothSurfaceCatalog::class, 'surface_code', 'code');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(DentistCommission::class);
    }

    public function supplyMovements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class, 'related_treatment_plan_item_id');
    }
}
