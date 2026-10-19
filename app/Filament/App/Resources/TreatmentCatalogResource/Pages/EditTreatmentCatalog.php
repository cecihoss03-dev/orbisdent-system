<?php

namespace App\Filament\App\Resources\TreatmentCatalogResource\Pages;

use App\Filament\App\Resources\TreatmentCatalogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTreatmentCatalog extends EditRecord
{
    protected static string $resource = TreatmentCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
