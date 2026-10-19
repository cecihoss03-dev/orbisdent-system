<?php

namespace App\Filament\App\Resources\TreatmentPlanResource\Pages;

use App\Filament\App\Resources\TreatmentPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreatmentPlans extends ListRecords
{
    protected static string $resource = TreatmentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
