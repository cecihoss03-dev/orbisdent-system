<?php

namespace App\Filament\App\Resources\SupplyCategoryResource\Pages;

use App\Filament\App\Resources\SupplyCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplyCategory extends EditRecord
{
    protected static string $resource = SupplyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
