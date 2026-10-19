<?php

namespace App\Filament\App\Resources\DentistScheduleResource\Pages;

use App\Filament\App\Resources\DentistScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDentistSchedules extends ListRecords
{
    protected static string $resource = DentistScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
