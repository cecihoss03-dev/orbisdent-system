<?php

namespace App\Filament\App\Resources\ChairResource\Pages;

use App\Filament\App\Resources\ChairResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChairs extends ListRecords
{
    protected static string $resource = ChairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
