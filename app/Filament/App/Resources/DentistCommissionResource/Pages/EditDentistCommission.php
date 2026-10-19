<?php

namespace App\Filament\App\Resources\DentistCommissionResource\Pages;

use App\Filament\App\Resources\DentistCommissionResource;
use Filament\Resources\Pages\EditRecord;

class EditDentistCommission extends EditRecord
{
    protected static string $resource = DentistCommissionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['status']);

        return CreateDentistCommission::recompute($data) + [];
    }
}
