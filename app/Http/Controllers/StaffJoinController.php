<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Registro público del personal: la persona crea su cuenta desde el enlace
 * de una clínica y queda "pendiente, sin rol". NO obtiene ningún acceso
 * hasta que el administrador de la clínica la apruebe y le asigne un rol.
 */
class StaffJoinController extends Controller
{
    public function show(string $code)
    {
        return view('join.form', ['tenant' => $this->tenant($code), 'code' => $code]);
    }

    public function store(Request $request, string $code)
    {
        $tenant = $this->tenant($code);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:150'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Str::lower(trim($data['email']));
        $user  = User::query()->where('email', $email)->first();

        if ($user) {
            // Cuenta existente: solo se vincula quien demuestre ser su dueño.
            if (! Hash::check($data['password'], $user->password)) {
                return back()->withInput()->withErrors([
                    'email' => 'Ya existe una cuenta con ese correo. Escribe la contraseña que ya usas en Orbis para unirte a esta clínica.',
                ]);
            }
        } else {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $email,
                'phone'    => $data['phone'] ?? null,
                'password' => $data['password'],
                'status'   => 'active',
            ]);
        }

        // RLS: fijamos la clínica solo durante este alta.
        TenantContext::set($tenant->id, $user->id);

        try {
            DB::transaction(function () use ($tenant, $user) {
                $exists = TenantUser::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)->where('user_id', $user->id)->exists();

                if (! $exists) {
                    TenantUser::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'user_id'   => $user->id,
                        'role_id'   => null,
                        'status'    => 'pending',
                    ]);
                }
            });
        } finally {
            TenantContext::clear();
        }

        return view('join.done', ['tenant' => $tenant]);
    }

    private function tenant(string $code): Tenant
    {
        abort_if(strlen($code) < 20, 404);

        return Tenant::query()
            ->where('invite_code', $code)
            ->whereIn('status', ['active', 'trial'])
            ->firstOrFail();
    }
}
