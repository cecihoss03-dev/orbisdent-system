<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Historial de pagos</title>
<style>
    @page { margin: 32px 34px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1f2937; }
    .brand { font-size: 20px; font-weight: bold; color: #4f46e5; }
    .muted { color: #6b7280; }
    h1 { font-size: 14px; margin: 2px 0 4px; }
    .filters { background: #f3f4f6; padding: 6px 10px; border-radius: 4px; margin: 10px 0 14px; }
    table.t { width: 100%; border-collapse: collapse; }
    table.t th { background: #eef2ff; color: #3730a3; text-align: left; padding: 6px; font-size: 8.5px; text-transform: uppercase; }
    table.t td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; }
    table.t tr:nth-child(even) td { background: #fafafa; }
    .right { text-align: right; }
    .sum { width: 46%; margin-top: 16px; border-collapse: collapse; }
    .sum td { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; }
    .grand td { font-weight: bold; font-size: 11px; color: #4f46e5; border-top: 2px solid #4f46e5; border-bottom: none; }
    .foot { position: fixed; bottom: -14px; left: 0; right: 0; text-align: center; font-size: 8px; color: #9ca3af; }
</style>
</head>
<body>
<table style="width:100%"><tr>
    <td><div class="brand">ORBIS</div><h1>Historial de pagos de suscripciones</h1></td>
    <td class="right muted">Generado: {{ now()->format('d/m/Y H:i') }}<br>{{ $rows->count() }} registro(s)</td>
</tr></table>

@if(!empty($filters))
<div class="filters"><strong>Filtros:</strong> {{ collect($filters)->map(fn($v, $k) => "$k: $v")->implode('  ·  ') }}</div>
@endif

<table class="t">
    <thead><tr>
        <th>Fecha</th><th>Clínica</th><th>Plan</th><th>Período</th><th>Método</th><th>Estado</th><th class="right">Monto</th>
    </tr></thead>
    <tbody>
    @forelse($rows as $r)
        <tr>
            <td>{{ $r->created_at?->format('d/m/Y') }}</td>
            <td>{{ $r->tenant?->business_name ?? '—' }}</td>
            <td>{{ $r->plan?->name ?? '—' }}</td>
            <td>{{ $r->start_date?->format('d/m/y') }} – {{ $r->end_date?->format('d/m/y') ?? '…' }}</td>
            <td>{{ $r->payment_method ?: '—' }}</td>
            <td>{{ ['active' => 'Activa', 'expired' => 'Expirada', 'cancelled' => 'Cancelada'][$r->status] ?? $r->status }}</td>
            <td class="right">{{ $symbol }} {{ number_format((float) $r->amount_paid, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="muted" style="text-align:center;padding:16px">Sin pagos para los filtros elegidos.</td></tr>
    @endforelse
    </tbody>
</table>

<table class="sum">
    @foreach($rows->groupBy(fn($r) => $r->payment_method ?: 'Sin método') as $method => $group)
        <tr><td>{{ $method }} ({{ $group->count() }})</td><td class="right">{{ $symbol }} {{ number_format((float) $group->sum('amount_paid'), 2) }}</td></tr>
    @endforeach
    <tr class="grand"><td>TOTAL</td><td class="right">{{ $symbol }} {{ number_format((float) $rows->sum('amount_paid'), 2) }}</td></tr>
</table>

<div class="foot">Orbis · Historial de pagos</div>
</body>
</html>
