<?php

namespace App\Filament\App\Resources\TreatmentCatalogResource\Pages;

use App\Filament\App\Resources\TreatmentCatalogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreatmentCatalogs extends ListRecords
{
    protected static string $resource = TreatmentCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
