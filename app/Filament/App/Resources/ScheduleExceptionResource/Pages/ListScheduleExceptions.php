<?php

namespace App\Filament\App\Resources\ScheduleExceptionResource\Pages;

use App\Filament\App\Resources\ScheduleExceptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScheduleExceptions extends ListRecords
{
    protected static string $resource = ScheduleExceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
