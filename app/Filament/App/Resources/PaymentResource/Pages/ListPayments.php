<?php

namespace App\Filament\App\Resources\PaymentResource\Pages;

use App\Filament\App\Resources\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
