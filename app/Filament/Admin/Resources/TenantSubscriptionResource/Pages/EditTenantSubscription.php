<?php

namespace App\Filament\Admin\Resources\TenantSubscriptionResource\Pages;

use App\Filament\Admin\Resources\TenantSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTenantSubscription extends EditRecord
{
    protected static string $resource = TenantSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
