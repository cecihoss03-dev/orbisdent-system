<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Support\Billing\InvoiceBalance;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // El servidor es la fuente de verdad de los montos.
        $subtotal = collect($data['items'] ?? [])->sum(
            fn ($i) => (float) $i['quantity'] * (float) $i['unit_price']
        );
        $data['subtotal'] = round($subtotal, 2);
        $data['total']    = round($subtotal + (float) ($data['tax'] ?? 0), 2);
        $data['status']   = 'issued';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Reintenta si dos facturas obtienen el mismo número a la vez
        // (índice único tenant_id + invoice_number).
        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(function () use ($data) {
                    $data['invoice_number'] = InvoiceBalance::nextNumber();

                    return static::getModel()::create($data);
                });
            } catch (QueryException $e) {
                if ($attempt >= 3) {
                    throw $e;
                }
            }
        }
    }
}
