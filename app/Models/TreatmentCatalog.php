<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentCatalog extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'category',
        'base_price',
        'estimated_duration_minutes',
        'requires_tooth',
        'is_active',
    ];

    protected $casts = [
        'base_price'      => 'decimal:2',
        'requires_tooth'  => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'treatment_id');
    }

    public function treatmentPlanItems(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class, 'treatment_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
