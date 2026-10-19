<?php

namespace App\Filament\App\Widgets;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Supply;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Gate;

class ClinicStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /** Solo se muestra si el rol puede ver al menos uno de los módulos que resume. */
    public static function canView(): bool
    {
        return Gate::allows('viewAny', Appointment::class)
            || Gate::allows('viewAny', Patient::class)
            || Gate::allows('viewAny', Invoice::class)
            || Gate::allows('viewAny', Supply::class);
    }

    protected function getStats(): array
    {
        $stats = [];

        if (Gate::allows('viewAny', Appointment::class)) {
            $today = Appointment::whereDate('scheduled_start', today());
            $stats[] = Stat::make('Citas de hoy', (clone $today)->count())
                ->description((clone $today)->whereIn('status', ['scheduled', 'confirmed'])->count().' por atender')
                ->icon('heroicon-o-calendar-days');
        }

        if (Gate::allows('viewAny', Patient::class)) {
            $stats[] = Stat::make('Pacientes', Patient::count())->icon('heroicon-o-users');
        }

        if (Gate::allows('viewAny', Invoice::class)) {
            $income = Payment::where('status', 'completed')
                ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount');
            $pending = Invoice::whereIn('status', ['issued', 'partially_paid'])->count();

            $stats[] = Stat::make('Ingresos del mes', '$'.number_format((float) $income, 2))
                ->icon('heroicon-o-banknotes')->color('success');
            $stats[] = Stat::make('Facturas con saldo', $pending)
                ->icon('heroicon-o-document-currency-dollar')
                ->color($pending ? 'warning' : 'success');
        }

        if (Gate::allows('viewAny', Supply::class)) {
            $low = Supply::whereColumn('current_stock', '<=', 'min_stock')->count();
            $stats[] = Stat::make('Insumos con stock bajo', $low)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($low ? 'danger' : 'success');
        }

        return $stats;
    }
}
