<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

/**
 * El puente del enfoque híbrido: Filament resuelve la clínica activa
 * (por la URL /app/{tenant}/... o la sesión) y aquí la traducimos al
 * mismo binding que el middleware ResolveTenantForNonFilamentRequests
 * usa para requests fuera de Filament (API, jobs, comandos).
 *
 * Resultado: el trait BelongsToTenant (Global Scope de Eloquent) es la
 * ÚNICA fuente de verdad para el scoping de datos en TODO el sistema —
 * Filament es solo uno de los "clientes" que alimenta ese binding, no
 * un mecanismo de scoping paralelo.
 */
class TenancyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::serving(function () {
            if (! Filament::hasTenancy()) {
                return;
            }

            if ($tenant = Filament::getTenant()) {
                app()->instance('currentTenantId', $tenant->getKey());
            }
        });
    }
}
