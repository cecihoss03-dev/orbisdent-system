<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo {{ $number }}</title>
<style>
    @page { margin: 36px 42px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }
    .head { width: 100%; border-bottom: 3px solid #4f46e5; padding-bottom: 12px; margin-bottom: 22px; }
    .head td { vertical-align: top; }
    .brand { font-size: 24px; font-weight: bold; color: #4f46e5; letter-spacing: 1px; }
    .muted { color: #6b7280; }
    .doc { text-align: right; }
    .doc .title { font-size: 15px; font-weight: bold; }
    h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin: 0 0 6px; }
    .box { border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 12px; margin-bottom: 18px; }
    .box p { margin: 2px 0; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.items th { background: #eef2ff; color: #3730a3; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; }
    table.items td { padding: 9px 8px; border-bottom: 1px solid #e5e7eb; }
    .right { text-align: right; }
    .total { width: 100%; margin-top: 16px; }
    .total td { padding: 4px 8px; }
    .total .big { font-size: 16px; font-weight: bold; color: #4f46e5; }
    .stamp { margin-top: 26px; padding: 8px 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 4px; font-weight: bold; text-align: center; }
    .foot { margin-top: 40px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>
<table class="head"><tr>
    <td><div class="brand">ORBIS</div><div class="muted">Software para clínicas odontológicas</div></td>
    <td class="doc"><div class="title">RECIBO DE PAGO</div>
        <div>N.º {{ $number }}</div>
        <div class="muted">Emitido: {{ $issuedAt->format('d/m/Y H:i') }}</div></td>
</tr></table>

<div class="box">
    <h3>Cliente</h3>
    <p><strong>{{ $subscription->tenant?->business_name ?? '—' }}</strong></p>
    @if($subscription->tenant?->tax_id)<p>NIT/RUC: {{ $subscription->tenant->tax_id }}</p>@endif
    @if($subscription->tenant?->address)<p>{{ $subscription->tenant->address }}</p>@endif
    @if($subscription->tenant?->phone)<p>Tel.: {{ $subscription->tenant->phone }}</p>@endif
</div>

<table class="items">
    <thead><tr><th>Concepto</th><th>Período</th><th>Método</th><th class="right">Monto</th></tr></thead>
    <tbody><tr>
        <td>Suscripción — Plan {{ $subscription->plan?->name ?? '—' }}
            @if($subscription->plan?->billing_cycle)<br><span class="muted">{{ $subscription->plan->billing_cycle === 'yearly' ? 'Facturación anual' : 'Facturación mensual' }}</span>@endif</td>
        <td>{{ $subscription->start_date?->format('d/m/Y') }} — {{ $subscription->end_date?->format('d/m/Y') ?? 'Sin fin' }}</td>
        <td>{{ $subscription->payment_method ?: '—' }}</td>
        <td class="right">{{ $symbol }} {{ number_format((float) $subscription->amount_paid, 2) }}</td>
    </tr></tbody>
</table>

<table class="total"><tr>
    <td class="right muted" style="width:75%">TOTAL PAGADO</td>
    <td class="right big">{{ $symbol }} {{ number_format((float) $subscription->amount_paid, 2) }}</td>
</tr></table>

<div class="stamp">PAGO REGISTRADO — {{ $subscription->created_at?->format('d/m/Y') }}</div>

<div class="foot">Documento generado por Orbis. Este recibo acredita el pago de la suscripción indicada y no reemplaza una factura fiscal.</div>
</body>
</html>
