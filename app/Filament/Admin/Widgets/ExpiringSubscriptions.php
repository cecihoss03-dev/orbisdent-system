<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TenantSubscription;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class ExpiringSubscriptions extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Suscripciones por vencer o vencidas (próximos 7 días)')
            ->query(fn () => TenantSubscription::query()
                ->with(['tenant', 'plan'])
                ->where('status', 'active')
                ->whereNotNull('end_date')
                ->where('end_date', '<=', today()->addDays(7))
                ->orderBy('end_date'))
            ->columns([
                TextColumn::make('tenant.business_name')->label('Clínica')->searchable(),
                TextColumn::make('tenant.phone')->label('Teléfono')->copyable()->placeholder('—'),
                TextColumn::make('plan.name')->label('Plan'),
                TextColumn::make('end_date')->label('Vence')->date('d/m/Y'),
                TextColumn::make('days_left')
                    ->label('Restan')
                    ->state(fn (TenantSubscription $record) => today()->diffInDays($record->end_date, false))
                    ->formatStateUsing(fn ($state) => $state < 0 ? 'Vencida hace '.abs($state).' d' : ($state == 0 ? 'Hoy' : "{$state} d"))
                    ->badge()
                    ->color(fn ($state) => $state <= 2 ? 'danger' : 'warning'),
            ])
            ->recordActions([
                Action::make('renovar')
                    ->label('Renovar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Se crea una nueva suscripción con el mismo plan y la actual queda como vencida.')
                    ->action(function (TenantSubscription $record) {
                        DB::transaction(function () use ($record) {
                            $start = $record->end_date->isPast() ? today() : $record->end_date->copy();
                            $end   = $record->plan?->billing_cycle === 'yearly'
                                ? $start->copy()->addYear()
                                : $start->copy()->addMonth();

                            TenantSubscription::create([
                                'tenant_id'      => $record->tenant_id,
                                'plan_id'        => $record->plan_id,
                                'start_date'     => $start,
                                'end_date'       => $end,
                                'status'         => 'active',
                                'amount_paid'    => $record->plan?->price,
                                'payment_method' => $record->payment_method,
                            ]);

                            $record->update(['status' => 'expired']);
                            $record->tenant?->update(['status' => 'active']);
                        });

                        Notification::make()->title('Suscripción renovada')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Ninguna suscripción por vencer')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
