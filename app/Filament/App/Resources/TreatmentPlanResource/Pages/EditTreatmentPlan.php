<?php

namespace App\Filament\App\Resources\TreatmentPlanResource\Pages;

use App\Filament\App\Resources\TreatmentPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTreatmentPlan extends EditRecord
{
    protected static string $resource = TreatmentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $itemsTotal = collect($data['items'] ?? [])->sum('subtotal');
        $data['total_amount'] = max(0, $itemsTotal - (float) ($data['discount_amount'] ?? 0));

        return $data;
    }
}
