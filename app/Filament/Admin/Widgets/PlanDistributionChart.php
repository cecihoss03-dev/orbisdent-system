<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Plan;
use Filament\Widgets\ChartWidget;

class PlanDistributionChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Clínicas por plan';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $plans = Plan::withCount(['tenants' => fn ($q) => $q->whereIn('status', ['active', 'trial'])])
            ->orderBy('price')->get();

        return [
            'datasets' => [[
                'label'           => 'Clínicas',
                'data'            => $plans->pluck('tenants_count')->all(),
                'backgroundColor' => ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7'],
            ]],
            'labels' => $plans->pluck('name')->all(),
        ];
    }
}
