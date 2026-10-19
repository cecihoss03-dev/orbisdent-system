<?php

namespace App\Filament\App\Resources\SupplyCategoryResource\Pages;

use App\Filament\App\Resources\SupplyCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplyCategories extends ListRecords
{
    protected static string $resource = SupplyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
