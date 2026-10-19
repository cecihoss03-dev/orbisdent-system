<?php

namespace App\Policies;

use App\Models\SystemAdmin;
use App\Support\Tenancy\TenantPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Base de las políticas por módulo: ver/crear/editar/eliminar se traducen a
 * los permisos "modulo.view|create|update|delete" del rol del usuario en la
 * clínica actual. El Admin de plataforma (SystemAdmin) pasa siempre.
 */
abstract class ModulePolicy
{
    abstract protected function module(): string;

    public function before(Authenticatable $user, string $ability): ?bool
    {
        return $user instanceof SystemAdmin ? true : null;
    }

    protected function can(Authenticatable $user, string $action): bool
    {
        return TenantPermissions::allows($user, $this->module().'.'.$action);
    }

    public function viewAny(Authenticatable $user): bool { return $this->can($user, 'view'); }
    public function view(Authenticatable $user, Model $model): bool { return $this->can($user, 'view'); }
    public function create(Authenticatable $user): bool { return $this->can($user, 'create'); }
    public function update(Authenticatable $user, Model $model): bool { return $this->can($user, 'update'); }
    public function delete(Authenticatable $user, Model $model): bool { return $this->can($user, 'delete'); }
    public function deleteAny(Authenticatable $user): bool { return $this->can($user, 'delete'); }
    public function restore(Authenticatable $user, Model $model): bool { return $this->can($user, 'update'); }
    public function restoreAny(Authenticatable $user): bool { return $this->can($user, 'update'); }
    public function forceDelete(Authenticatable $user, Model $model): bool { return $this->can($user, 'delete'); }
    public function forceDeleteAny(Authenticatable $user): bool { return $this->can($user, 'delete'); }
    public function reorder(Authenticatable $user): bool { return $this->can($user, 'update'); }
}
