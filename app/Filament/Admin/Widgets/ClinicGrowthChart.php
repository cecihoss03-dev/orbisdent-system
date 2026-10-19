<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use Filament\Widgets\ChartWidget;

class ClinicGrowthChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Clínicas nuevas por mes';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $from = now()->startOfMonth()->subMonths(11);

        $rows = Tenant::query()
            ->where('created_at', '>=', $from)
            ->selectRaw("to_char(created_at, 'YYYY-MM') as ym, count(*) as c")
            ->groupBy('ym')
            ->pluck('c', 'ym');

        $labels = [];
        $data   = [];
        for ($i = 0; $i < 12; $i++) {
            $m        = $from->copy()->addMonths($i);
            $labels[] = $m->translatedFormat('M y');
            $data[]   = (int) ($rows[$m->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [['label' => 'Clínicas nuevas', 'data' => $data, 'tension' => 0.3]],
            'labels'   => $labels,
        ];
    }
}
