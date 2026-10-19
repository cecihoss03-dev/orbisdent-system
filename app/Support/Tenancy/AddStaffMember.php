<?php

namespace App\Support\Tenancy;

use App\Models\TenantUser;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Agrega a una persona al personal de una clínica, identificándola por
 * correo: reutiliza su cuenta si ya existe (un odontólogo puede trabajar
 * en varias clínicas) o crea una nueva con contraseña aleatoria.
 *
 * Existe para NO listar todos los usuarios de la plataforma en un
 * desplegable: cada clínica solo ve a su propio personal.
 */
class AddStaffMember
{
    /**
     * @param  array{email: string, name?: ?string, role_id: int|string, status?: string}  $data
     *
     * @throws DomainException si la persona ya pertenece a esa clínica
     */
    public static function handle(int|string $tenantId, array $data): TenantUser
    {
        return DB::transaction(function () use ($tenantId, $data) {
            $email = Str::lower(trim($data['email']));

            $user = User::query()->where('email', $email)->first()
                ?? User::create([
                    'name'     => filled($data['name'] ?? null) ? $data['name'] : Str::before($email, '@'),
                    'email'    => $email,
                    'password' => Str::random(40),
                    'status'   => 'active',
                ]);

            $alreadyMember = TenantUser::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyMember) {
                throw new DomainException('Esa persona ya pertenece a esta clínica.');
            }

            return TenantUser::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'user_id'   => $user->id,
                'role_id'   => $data['role_id'],
                'status'    => $data['status'] ?? 'active',
            ]);
        });
    }
}
