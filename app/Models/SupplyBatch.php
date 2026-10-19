<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyBatch extends Model
{
    const CREATED_AT = 'received_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'supply_id',
        'supplier_id',
        'batch_number',
        'expiration_date',
        'quantity_received',
        'quantity_remaining',
    ];

    protected $casts = [
        'expiration_date'    => 'date',
        'quantity_received'  => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'received_at'        => 'datetime',
    ];

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(SupplyMovement::class, 'batch_id');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiration_date')
            ->where('quantity_remaining', '>', 0)
            ->whereDate('expiration_date', '<=', now()->addDays($days));
    }
}
