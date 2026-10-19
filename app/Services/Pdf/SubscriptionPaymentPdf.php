<?php

namespace App\Services\Pdf;

use App\Models\TenantSubscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class SubscriptionPaymentPdf
{
    public static function receiptNumber(TenantSubscription $s): string
    {
        return 'R-'.str_pad((string) $s->id, 6, '0', STR_PAD_LEFT);
    }

    public static function receipt(TenantSubscription $s): string
    {
        $s->loadMissing(['tenant', 'plan']);

        return Pdf::loadView('pdf.subscription-receipt', [
            'subscription' => $s,
            'number'       => self::receiptNumber($s),
            'issuedAt'     => now(),
            'symbol'       => config('orbis.currency_symbol', 'Bs.'),
        ])->setPaper('a4')->output();
    }

    /** @param Collection<int, TenantSubscription> $rows */
    public static function report(Collection $rows, array $filters = []): string
    {
        $rows->loadMissing(['tenant', 'plan']);

        return Pdf::loadView('pdf.subscription-payments-report', [
            'rows'    => $rows,
            'filters' => $filters,
            'symbol'  => config('orbis.currency_symbol', 'Bs.'),
        ])->setPaper('a4', 'landscape')->output();
    }

    /** Respuesta de descarga lista para devolver desde una acción de Filament. */
    public static function download(string $binary, string $filename)
    {
        return response()->streamDownload(fn () => print($binary), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
