<?php

namespace App\Filament\App\Resources\RoleResource\Pages;

use App\Filament\App\Resources\RoleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * Un rol creado desde una clínica es siempre propio de esa clínica.
     * tenant_id se rellena a mano porque este Resource no está scopeado
     * a tenant (ver RoleResource).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()?->getKey();
        $data['is_system'] = false;

        return $data;
    }
}
