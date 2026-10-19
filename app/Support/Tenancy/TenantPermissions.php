<?php

namespace App\Support\Tenancy;

use App\Models\Role;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Permisos del usuario DENTRO de la clínica activa (rol -> permisos).
 * El resultado se cachea por petición (en los atributos del Request),
 * así que un cambio de rol se ve en la siguiente petición.
 */
class TenantPermissions
{
    public const ADMIN_ROLE = 'Administrador de clínica';

    public static function allows(?Authenticatable $user, string $permission): bool
    {
        if (! $user instanceof User || ! app()->bound('currentTenantId')) {
            return false;
        }

        $set = self::forUser($user, (int) app('currentTenantId'));

        return $set === '*' || isset($set[$permission]);
    }

    /** @return array<string,int>|'*' */
    private static function forUser(User $user, int $tenantId): array|string
    {
        $bag = request()->attributes;
        $key = "orbis.permissions.{$user->getKey()}.{$tenantId}";

        if (! $bag->has($key)) {
            $bag->set($key, self::load($user->getKey(), $tenantId));
        }

        return $bag->get($key);
    }

    private static function load(int|string $userId, int $tenantId): array|string
    {
        $membership = TenantUser::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            return [];
        }

        $role = Role::find($membership->role_id);

        if (! $role) {
            return [];
        }

        // El administrador de la clínica tiene todo, incluso permisos creados después.
        if ($role->is_system && $role->tenant_id === null && $role->name === self::ADMIN_ROLE) {
            return '*';
        }

        return $role->permissions()->pluck('permissions.name')->flip()->all();
    }
}
