<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (session()->has('tenant_id') && !$model->tenant_id) {
                $model->tenant_id = session('tenant_id');
            }
        });
    }
}