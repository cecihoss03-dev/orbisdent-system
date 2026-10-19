<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Patient;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $cur = config('orbis.currency_symbol', 'Bs.');

        $byStatus = Tenant::query()->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $active   = (int) ($byStatus['active'] ?? 0);
        $trial    = (int) ($byStatus['trial'] ?? 0);
        $suspended = (int) ($byStatus['suspended'] ?? 0);

        // MRR: suscripciones activas vigentes, anuales prorrateadas /12.
        $mrr = TenantSubscription::with('plan')
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()))
            ->get()
            ->sum(fn ($s) => $s->plan
                ? ($s->plan->billing_cycle === 'yearly' ? $s->plan->price / 12 : $s->plan->price)
                : 0);

        $expiring7 = TenantSubscription::where('status', 'active')
            ->whereBetween('end_date', [today(), today()->addDays(7)])->count();

        $expiredActive = TenantSubscription::where('status', 'active')
            ->where('end_date', '<', today())->count();

        $newThisMonth = Tenant::where('created_at', '>=', now()->startOfMonth())->count();

        return [
            Stat::make('Clínicas', $active + $trial + $suspended)
                ->description("{$active} activas · {$trial} en prueba · {$suspended} suspendidas")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Ingreso mensual recurrente', $cur.' '.number_format((float) $mrr, 2))
                ->description('Suscripciones vigentes (anuales /12)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Vencen en 7 días', $expiring7)
                ->description($expiredActive ? "{$expiredActive} ya vencidas sin renovar" : 'Sin vencidas pendientes')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiredActive ? 'danger' : ($expiring7 ? 'warning' : 'success')),

            Stat::make('Nuevas este mes', $newThisMonth)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            // Sin contexto de clínica el Global Scope no filtra: cuenta toda la plataforma.
            Stat::make('Pacientes en la plataforma', number_format(Patient::count()))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('Usuarios', number_format(User::count()))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('gray'),
        ];
    }
}
