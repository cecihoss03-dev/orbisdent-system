<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    const UPDATED_AT = null; // la tabla solo registra created_at

    // Nota: NO usa el trait BelongsToTenant a propósito. tenant_id es
    // nullable aquí (NULL = rol de sistema, disponible para todas las
    // clínicas); un Global Scope que filtre por tenant_id excluiría los
    // roles de sistema. Usa scopeAvailableFor() en su lugar.

    protected $fillable = [
        'tenant_id',
        'name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Roles utilizables por un tenant dado: los de sistema (tenant_id
     * NULL) más los custom creados por esa clínica.
     */
    public function scopeAvailableFor($query, ?int $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
        });
    }
}
