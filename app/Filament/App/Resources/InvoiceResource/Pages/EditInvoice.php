<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subtotal = collect($data['items'] ?? [])->sum(
            fn ($i) => (float) $i['quantity'] * (float) $i['unit_price']
        );
        $data['subtotal'] = round($subtotal, 2);
        $data['total']    = round($subtotal + (float) ($data['tax'] ?? 0), 2);

        return $data;
    }
}
