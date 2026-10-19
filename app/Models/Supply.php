<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supply extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'unit_measure', // unidad, caja, ml, gr
        'min_stock',
        'current_stock',
        'unit_cost',
        'tracks_expiration',
    ];

    protected $casts = [
        'min_stock'         => 'decimal:2',
        'current_stock'     => 'decimal:2',
        'unit_cost'         => 'decimal:2',
        'tracks_expiration' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupplyCategory::class, 'category_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SupplyBatch::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock');
    }
}
