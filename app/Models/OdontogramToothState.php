<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramToothState extends Model
{
    protected $table = 'odontogram_tooth_states';

    public $timestamps = false;

    protected $fillable = [
        'odontogram_id',
        'tooth_fdi_code',
        'surface_code',
        'condition_type_id',
        'notes',
        'color_override',
    ];

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(ToothDefinition::class, 'tooth_fdi_code', 'fdi_code');
    }

    public function surface(): BelongsTo
    {
        return $this->belongsTo(ToothSurfaceCatalog::class, 'surface_code', 'code');
    }

    public function conditionType(): BelongsTo
    {
        return $this->belongsTo(ConditionTypeCatalog::class, 'condition_type_id');
    }

    /** El hallazgo aplica a la pieza completa (no a una cara específica). */
    public function scopeWholeTooth($query)
    {
        return $query->whereNull('surface_code');
    }
}