<?php

namespace App\Scopes;

use App\Models\User;
use App\Support\Tenancy\TenantPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sin el permiso "patients.view_all" el usuario solo ve a los pacientes que
 * tienen (o tuvieron) citas asignadas a él. Pensado para el especialista externo.
 */
class AssignedPatientsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::guard('web')->user();

        if (! $user instanceof User || ! app()->bound('currentTenantId')) {
            return;
        }

        if (TenantPermissions::allows($user, 'patients.view_all')) {
            return;
        }

        $builder->whereIn(
            $model->getTable().'.id',
            DB::table('appointments')
                ->where('tenant_id', app('currentTenantId'))
                ->where('dentist_id', $user->getKey())
                ->select('patient_id')
        );
    }
}
