<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setea el mismo binding que usa el puente de Filament
 * (App\Providers\TenancyServiceProvider), pero para requests que NO
 * pasan por un panel de Filament: una API REST para la app móvil, un
 * webhook, etc.
 *
 * AJUSTA la resolución de abajo a tu estrategia real de autenticación:
 * ahora mismo asume que el usuario autenticado pertenece a una sola
 * clínica activa y toma esa. Si permites elegir clínica en el login (un
 * odontólogo con varias clínicas, usando la API), reemplaza esto por
 * leer el tenant desde un header, el token, o la sesión.
 */
class ResolveTenantForNonFilamentRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $tenantId = $user->tenants()
                ->wherePivot('status', 'active')
                ->value('tenants.id');

            if ($tenantId) {
                app()->instance('currentTenantId', $tenantId);
            }
        }

        return $next($request);
    }
}
