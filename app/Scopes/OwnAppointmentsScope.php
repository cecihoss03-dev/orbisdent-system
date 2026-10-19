<?php

namespace App\Scopes;

use App\Models\User;
use App\Support\Tenancy\TenantPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/** Sin "appointments.view_all" el usuario solo ve las citas asignadas a él. */
class OwnAppointmentsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::guard('web')->user();

        if (! $user instanceof User || ! app()->bound('currentTenantId')) {
            return;
        }

        if (TenantPermissions::allows($user, 'appointments.view_all')) {
            return;
        }

        $builder->where($model->getTable().'.dentist_id', $user->getKey());
    }
}
