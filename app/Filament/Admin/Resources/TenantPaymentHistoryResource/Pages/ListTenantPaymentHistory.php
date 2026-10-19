<?php

namespace App\Filament\Admin\Resources\TenantPaymentHistoryResource\Pages;

use App\Filament\Admin\Resources\TenantPaymentHistoryResource;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\Pdf\SubscriptionPaymentPdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListTenantPaymentHistory extends ListRecords
{
    protected static string $resource = TenantPaymentHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar reporte PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->modalHeading('Exportar historial de pagos')
                ->modalSubmitActionLabel('Descargar PDF')
                ->schema([
                    Select::make('tenant_id')->label('Clínica (opcional)')
                        ->options(fn () => Tenant::orderBy('business_name')->pluck('business_name', 'id'))
                        ->searchable(),
                    Select::make('plan_id')->label('Plan (opcional)')
                        ->options(fn () => Plan::orderBy('name')->pluck('name', 'id')),
                    DatePicker::make('desde')->label('Desde')->native(false)->default(now()->startOfMonth()),
                    DatePicker::make('hasta')->label('Hasta')->native(false)->default(now()),
                ])
                ->action(function (array $data) {
                    $rows = TenantSubscription::query()
                        ->with(['tenant', 'plan'])
                        ->where('amount_paid', '>', 0)
                        ->when($data['tenant_id'] ?? null, fn ($q, $v) => $q->where('tenant_id', $v))
                        ->when($data['plan_id'] ?? null, fn ($q, $v) => $q->where('plan_id', $v))
                        ->when($data['desde'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['hasta'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
                        ->orderByDesc('created_at')
                        ->get();

                    $filters = array_filter([
                        'Clínica' => ! empty($data['tenant_id']) ? Tenant::find($data['tenant_id'])?->business_name : null,
                        'Plan'    => ! empty($data['plan_id']) ? Plan::find($data['plan_id'])?->name : null,
                        'Desde'   => ! empty($data['desde']) ? date('d/m/Y', strtotime($data['desde'])) : null,
                        'Hasta'   => ! empty($data['hasta']) ? date('d/m/Y', strtotime($data['hasta'])) : null,
                    ]);

                    return SubscriptionPaymentPdf::download(
                        SubscriptionPaymentPdf::report($rows, $filters),
                        'historial-pagos-'.now()->format('Ymd-His').'.pdf'
                    );
                }),
        ];
    }
}
