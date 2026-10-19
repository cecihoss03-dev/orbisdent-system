<?php

namespace App\Support\Tenancy;

use App\Models\TenantUser;
use Illuminate\Database\Eloquent\Builder;

class TenantStaff
{
    /**
     * Subconsulta con los user_id de los ODONTÓLOGOS de la clínica actual:
     * personal activo con rol "Odontólogo" o con perfil de odontólogo.
     * Se usa para que los desplegables de odontólogo NO listen usuarios de
     * otras clínicas (la tabla users es global).
     */
    public static function dentistUserIds(): Builder
    {
        return TenantUser::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q
                ->whereHas('role', fn ($r) => $r->where('name', 'Odontólogo'))
                ->orWhereHas('user.dentistProfile'))
            ->select('user_id');
    }
}
