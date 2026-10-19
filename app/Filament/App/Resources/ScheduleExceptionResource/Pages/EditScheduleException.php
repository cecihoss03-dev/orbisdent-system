<?php

namespace App\Filament\App\Resources\ScheduleExceptionResource\Pages;

use App\Filament\App\Resources\ScheduleExceptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScheduleException extends EditRecord
{
    protected static string $resource = ScheduleExceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
