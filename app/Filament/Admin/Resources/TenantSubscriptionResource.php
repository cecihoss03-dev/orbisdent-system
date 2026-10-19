<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantSubscriptionResource\Pages;
use App\Models\TenantSubscription;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class TenantSubscriptionResource extends Resource
{
    protected static ?string $model = TenantSubscription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación SaaS';

    protected static ?string $modelLabel = 'suscripción';

    protected static ?string $pluralModelLabel = 'suscripciones';

    protected static ?int $navigationSort = 2;

    private const STATUSES = [
        'active'    => 'Activa',
        'expired'   => 'Expirada',
        'cancelled' => 'Cancelada',
    ];

    /** Suscripciones activas que vencen en N días o menos (o ya vencidas sin renovar). */
    public static function expiringQuery(): Builder
    {
        return TenantSubscription::query()
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', today()->addDays((int) config('orbis.subscriptions.alert_days_before', 2)));
    }

    public static function getNavigationBadge(): ?string
    {
        $n = static::expiringQuery()->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Suscripciones por vencer';
    }

    /** Enlace wa.me al teléfono de la clínica con el aviso ya redactado. */
    public static function whatsappUrl(TenantSubscription $record): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $record->tenant?->phone);
        if (! $digits) {
            return null;
        }
        if (strlen($digits) === 8) {
            $digits = '591'.$digits; // celulares de Bolivia sin código de país
        }

        $text = "Hola, le escribimos de Orbis. La suscripción de {$record->tenant->business_name} vence el "
            .$record->end_date->format('d/m/Y').'. ¿Desea renovarla para no perder el acceso?';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($text);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tenant_id')
                ->label('Clínica')
                ->relationship('tenant', 'business_name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('plan_id')
                ->label('Plan')
                ->relationship('plan', 'name')
                ->required(),

            DatePicker::make('start_date')->label('Inicio')->required()->native(false),

            DatePicker::make('end_date')->label('Fin')->native(false),

            Select::make('status')
                ->label('Estado')
                ->options(self::STATUSES)
                ->required()
                ->default('active'),

            TextInput::make('amount_paid')->label('Monto pagado')->numeric()->prefix('$'),

            TextInput::make('payment_method')->label('Método de pago'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.business_name')->label('Clínica')->searchable()->sortable(),
                TextColumn::make('plan.name')->label('Plan')->badge(),
                TextColumn::make('start_date')->label('Inicio')->date()->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(function (TenantSubscription $record) {
                        if ($record->status !== 'active' || ! $record->end_date) {
                            return null;
                        }
                        $left = today()->diffInDays($record->end_date, false);

                        return match (true) {
                            $left < 0   => 'Vencida hace '.abs($left).' día(s)',
                            $left === 0 => 'Vence HOY',
                            $left <= 7  => "Vence en {$left} día(s)",
                            default     => null,
                        };
                    })
                    ->color(fn (TenantSubscription $record) => $record->status === 'active' && $record->end_date
                        && today()->diffInDays($record->end_date, false) <= (int) config('orbis.subscriptions.alert_days_before', 2)
                        ? 'danger' : null),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'expired'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('amount_paid')->label('Monto')->money('usd'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(self::STATUSES),
                Filter::make('por_vencer')
                    ->label('Por vencer / vencidas sin renovar')
                    ->query(fn (Builder $query) => $query->where('status', 'active')
                        ->whereNotNull('end_date')
                        ->where('end_date', '<=', today()->addDays((int) config('orbis.subscriptions.alert_days_before', 2)))),
            ])
            ->recordClasses(fn (TenantSubscription $record) => $record->status === 'active' && $record->end_date
                && today()->diffInDays($record->end_date, false) <= (int) config('orbis.subscriptions.alert_days_before', 2)
                ? 'bg-danger-50 dark:bg-danger-950/30' : null)
            ->recordActions([
                Action::make('whatsapp')
                    ->label('Avisar')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn (TenantSubscription $record) => static::whatsappUrl($record), shouldOpenInNewTab: true)
                    ->visible(fn (TenantSubscription $record) => $record->status === 'active' && static::whatsappUrl($record) !== null),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenantSubscriptions::route('/'),
            'create' => Pages\CreateTenantSubscription::route('/create'),
            'edit'   => Pages\EditTenantSubscription::route('/{record}/edit'),
        ];
    }
}
