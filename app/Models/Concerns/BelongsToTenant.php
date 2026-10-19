<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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