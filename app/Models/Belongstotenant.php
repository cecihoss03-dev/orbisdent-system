<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aplica automáticamente `WHERE tenant_id = ?` a todas las queries del
 * modelo (Global Scope) y rellena tenant_id al crear un registro nuevo.
 *
 * Requiere que algo (middleware de auth / resolución de tenant) haga:
 *   app()->instance('currentTenantId', $tenantId);
 * al inicio del request. Ajusta esa resolución a tu mecanismo real
 * (subdominio, header, sesión del usuario autenticado, etc.).
 *
 * Esta es la primera capa de aislamiento; la Row Level Security de
 * PostgreSQL (definida en el DDL) actúa como segunda capa de defensa.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('currentTenantId')) {
                $builder->where(
                    $builder->getModel()->getTable().'.tenant_id',
                    app('currentTenantId')
                );
            }
        });

        static::creating(function ($model) {
            if (empty($model->tenant_id) && app()->bound('currentTenantId')) {
                $model->tenant_id = app('currentTenantId');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}