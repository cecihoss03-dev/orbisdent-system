<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantPaymentHistoryResource\Pages;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\Pdf\SubscriptionPaymentPdf;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Vista de SOLO LECTURA sobre tenant_subscriptions: cada suscripción con
 * monto pagado es un pago de la clínica a Orbis. Se edita desde "Suscripciones".
 */
class TenantPaymentHistoryResource extends Resource
{
    protected static ?string $model = TenantSubscription::class;

    protected static ?string $slug = 'historial-pagos';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación SaaS';

    protected static ?string $modelLabel = 'pago';

    protected static ?string $pluralModelLabel = 'historial de pagos';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('amount_paid')->where('amount_paid', '>', 0);
    }

    private static function money($v): string
    {
        return config('orbis.currency_symbol', 'Bs.').' '.number_format((float) $v, 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tenant', 'plan']))
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),

                TextColumn::make('tenant.business_name')->label('Clínica')->searchable(),

                TextColumn::make('plan.name')->label('Plan')->badge(),

                TextColumn::make('period')
                    ->label('Período')
                    ->state(fn (TenantSubscription $record) => $record->start_date?->format('d/m/Y').' – '.($record->end_date?->format('d/m/Y') ?? '…')),

                TextColumn::make('payment_method')->label('Método')->placeholder('—')->searchable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ['active' => 'Activa', 'expired' => 'Expirada', 'cancelled' => 'Cancelada'][$state] ?? $state)
                    ->color(fn ($state) => ['active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'][$state] ?? 'gray'),

                TextColumn::make('amount_paid')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->formatStateUsing(fn ($state) => self::money($state))),
            ])
            ->filters([
                SelectFilter::make('tenant_id')->label('Clínica')
                    ->options(fn () => Tenant::orderBy('business_name')->pluck('business_name', 'id'))
                    ->searchable(),

                SelectFilter::make('plan_id')->label('Plan')
                    ->options(fn () => Plan::orderBy('name')->pluck('name', 'id')),

                SelectFilter::make('payment_method')->label('Método')
                    ->options(fn () => TenantSubscription::whereNotNull('payment_method')->distinct()->pluck('payment_method', 'payment_method')),

                Filter::make('rango')
                    ->label('Rango de fechas')
                    ->schema([
                        DatePicker::make('desde')->label('Desde')->native(false),
                        DatePicker::make('hasta')->label('Hasta')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['hasta'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                Action::make('recibo')
                    ->label('Recibo PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn (TenantSubscription $record) => SubscriptionPaymentPdf::download(
                        SubscriptionPaymentPdf::receipt($record),
                        'recibo-'.SubscriptionPaymentPdf::receiptNumber($record).'.pdf'
                    )),
            ])
            ->toolbarActions([
                BulkAction::make('pdf_seleccion')
                    ->label('Descargar PDF de los seleccionados')
                    ->icon('heroicon-o-document-arrow-down')
                    ->deselectRecordsAfterCompletion()
                    ->action(fn (Collection $records) => SubscriptionPaymentPdf::download(
                        SubscriptionPaymentPdf::report($records->sortByDesc('created_at')->values(), ['Selección' => $records->count().' pago(s)']),
                        'historial-pagos-'.now()->format('Ymd-His').'.pdf'
                    )),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantPaymentHistory::route('/'),
        ];
    }
}
