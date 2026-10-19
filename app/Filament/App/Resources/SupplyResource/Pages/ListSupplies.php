<?php

namespace App\Filament\App\Resources\SupplyResource\Pages;

use App\Filament\App\Resources\SupplyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplies extends ListRecords
{
    protected static string $resource = SupplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
