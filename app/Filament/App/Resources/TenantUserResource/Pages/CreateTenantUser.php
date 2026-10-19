<?php

namespace App\Filament\App\Resources\TenantUserResource\Pages;

use App\Filament\App\Resources\TenantUserResource;
use App\Support\Tenancy\AddStaffMember;
use DomainException;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenantUser extends CreateRecord
{
    protected static string $resource = TenantUserResource::class;

    /**
     * El formulario pide correo, no un usuario de una lista: así una
     * clínica nunca ve a las personas de otras clínicas.
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return AddStaffMember::handle(Filament::getTenant()->getKey(), $data);
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            $this->halt();
        }
    }
}
