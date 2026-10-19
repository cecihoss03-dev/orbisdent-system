<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TenantSubscription;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Ingresos por suscripciones (12 meses)';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $from = now()->startOfMonth()->subMonths(11);

        $rows = TenantSubscription::query()
            ->where('start_date', '>=', $from)
            ->whereNotNull('amount_paid')
            ->selectRaw("to_char(start_date, 'YYYY-MM') as ym, sum(amount_paid) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $data   = [];
        for ($i = 0; $i < 12; $i++) {
            $m        = $from->copy()->addMonths($i);
            $labels[] = $m->translatedFormat('M y');
            $data[]   = (float) ($rows[$m->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Ingresos ('.config('orbis.currency_symbol', 'Bs.').')',
                'data'  => $data,
            ]],
            'labels' => $labels,
        ];
    }
}
