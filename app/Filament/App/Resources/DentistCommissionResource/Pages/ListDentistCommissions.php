<?php

namespace App\Filament\App\Resources\DentistCommissionResource\Pages;

use App\Filament\App\Resources\DentistCommissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDentistCommissions extends ListRecords
{
    protected static string $resource = DentistCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
