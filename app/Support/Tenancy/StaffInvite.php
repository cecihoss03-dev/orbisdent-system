<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Str;

/** Enlace público de registro del personal de una clínica. */
class StaffInvite
{
    public static function code(Tenant $tenant): string
    {
        if (blank($tenant->invite_code)) {
            return self::regenerate($tenant);
        }

        return $tenant->invite_code;
    }

    public static function regenerate(Tenant $tenant): string
    {
        $code = Str::random(32);
        $tenant->forceFill(['invite_code' => $code])->save();

        return $code;
    }

    public static function url(Tenant $tenant): string
    {
        return url('/unirse/'.self::code($tenant));
    }
}
