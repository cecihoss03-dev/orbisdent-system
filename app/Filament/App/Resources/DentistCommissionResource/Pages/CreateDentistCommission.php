<?php

namespace App\Filament\App\Resources\DentistCommissionResource\Pages;

use App\Filament\App\Resources\DentistCommissionResource;
use App\Models\TreatmentPlanItem;
use Filament\Resources\Pages\CreateRecord;

class CreateDentistCommission extends CreateRecord
{
    protected static string $resource = DentistCommissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $ok = \App\Models\TenantUser::where('tenant_id', app('currentTenantId'))
            ->where('user_id', $data['user_id'] ?? 0)->exists();
        abort_unless($ok, 403);

        return static::recompute($data);
    }

    /** Recalcula server-side el monto: el cliente no es fuente de verdad. */
    public static function recompute(array $data): array
    {
        $item  = TreatmentPlanItem::find($data['treatment_plan_item_id'] ?? null);
        $value = (float) ($data['commission_value'] ?? 0);

        $data['calculated_amount'] = round(
            ($data['commission_type'] ?? '') === 'percentage'
                ? ($item ? (float) $item->subtotal * $value / 100 : 0)
                : $value,
            2
        );
        $data['status'] = 'pending';

        return $data;
    }
}
