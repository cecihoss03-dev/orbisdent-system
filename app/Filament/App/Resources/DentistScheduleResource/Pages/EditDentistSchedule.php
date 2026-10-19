<?php

namespace App\Filament\App\Resources\DentistScheduleResource\Pages;

use App\Filament\App\Resources\DentistScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDentistSchedule extends EditRecord
{
    protected static string $resource = DentistScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
