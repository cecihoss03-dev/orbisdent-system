<?php

namespace App\Support\Billing;

use App\Models\Invoice;
use App\Models\Payment;

class InvoiceBalance
{
    public static function paid(Invoice $invoice): float
    {
        return (float) Payment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public static function balance(Invoice $invoice): float
    {
        return max(0, round((float) $invoice->total - self::paid($invoice), 2));
    }

    /** Recalcula el estado de la factura según los pagos completados. */
    public static function sync(?Invoice $invoice): void
    {
        if (! $invoice || $invoice->status === 'void') {
            return;
        }

        $paid = self::paid($invoice);

        $status = match (true) {
            $paid >= (float) $invoice->total && $paid > 0 => 'paid',
            $paid > 0                                     => 'partially_paid',
            default                                       => 'issued',
        };

        if ($invoice->status !== $status) {
            $invoice->forceFill(['status' => $status])->saveQuietly();
        }
    }

    /** Número correlativo por clínica: F-2026-000001 */
    public static function nextNumber(): string
    {
        $prefix = 'F-'.now()->year.'-';

        $last = Invoice::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
