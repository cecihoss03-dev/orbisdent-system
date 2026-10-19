<?php

namespace App\Filament\App\Resources\TreatmentPlanResource\Pages;

use App\Filament\App\Resources\TreatmentPlanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTreatmentPlan extends CreateRecord
{
    protected static string $resource = TreatmentPlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        // Respaldo server-side: el total ya viene calculado por la
        // reactividad del formulario, pero lo recalculamos acá también
        // por si algún borde de la UI no disparó el recálculo.
        $itemsTotal = collect($data['items'] ?? [])->sum('subtotal');
        $data['total_amount'] = max(0, $itemsTotal - (float) ($data['discount_amount'] ?? 0));

        return $data;
    }
}
