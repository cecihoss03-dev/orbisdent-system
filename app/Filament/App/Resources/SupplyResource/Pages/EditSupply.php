<?php

namespace App\Filament\App\Resources\SupplyResource\Pages;

use App\Filament\App\Resources\SupplyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupply extends EditRecord
{
    protected static string $resource = SupplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Un insumo con movimientos no se elimina: el kardex es un
            // registro contable y la base de datos lo protege.
            DeleteAction::make()
                ->visible(fn () => $this->record->movements()->doesntExist()),
        ];
    }
}
