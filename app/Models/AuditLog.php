<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const UPDATED_AT = null; // la tabla solo registra created_at

    // Nota: sin trait BelongsToTenant a propósito — tenant_id es
    // nullable (acciones del Admin General no pertenecen a ningún
    // tenant) y esta tabla suele consultarse por entity_type/entity_id,
    // no por el tenant "actual" de la sesión.

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action', // create, update, delete
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }
}
