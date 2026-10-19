<?php

namespace App\Filament\Admin\Resources\TenantSubscriptionResource\Pages;

use App\Filament\Admin\Resources\TenantSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTenantSubscriptions extends ListRecords
{
    protected static string $resource = TenantSubscriptionResource::class;

    public function mount(): void
    {
        parent::mount();

        // Alerta automática al abrir la página, con las clínicas afectadas.
        $expiring = TenantSubscriptionResource::expiringQuery()->with('tenant')->orderBy('end_date')->get();

        if ($expiring->isNotEmpty()) {
            $days = (int) config('orbis.subscriptions.alert_days_before', 2);

            Notification::make()
                ->title($expiring->count().' suscripción(es) vencen en '.$days.' días o menos')
                ->body($expiring->take(6)->map(fn ($s) => '• '.$s->tenant?->business_name.' — '.$s->end_date->format('d/m/Y'))->implode("\n"))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
