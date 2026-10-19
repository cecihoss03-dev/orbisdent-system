<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DentistCommissionResource\Pages;
use App\Models\DentistCommission;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use App\Support\Tenancy\TenantStaff;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DentistCommissionResource extends Resource
{
    protected static ?string $model = DentistCommission::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación';

    protected static ?string $modelLabel = 'comisión';

    protected static ?string $pluralModelLabel = 'comisiones';

    protected static ?int $navigationSort = 3;

    /** Calcula el monto según tipo y valor, sobre el subtotal del ítem. */
    public static function compute(Get $get, Set $set): void
    {
        $item  = $get('treatment_plan_item_id') ? TreatmentPlanItem::find($get('treatment_plan_item_id')) : null;
        $value = (float) ($get('commission_value') ?? 0);

        $amount = $get('commission_type') === 'percentage'
            ? ($item ? (float) $item->subtotal * $value / 100 : 0)
            : $value;

        $set('calculated_amount', round($amount, 2));
    }

    /** Odontólogos de la clínica actual (User es global: se filtra por tenant_users). */
    protected static function dentists()
    {
        return User::query()
            ->whereIn('id', TenantStaff::dentistUserIds())
            ->orderBy('name');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Odontólogo')
                ->options(fn () => static::dentists()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('treatment_plan_item_id')
                ->label('Tratamiento realizado')
                ->options(fn () => TreatmentPlanItem::with(['treatment', 'treatmentPlan.patient'])
                    ->where('status', 'completed')
                    ->latest('id')->limit(200)->get()
                    ->mapWithKeys(fn ($i) => [
                        $i->id => ($i->treatmentPlan?->patient?->full_name ?? '—').' · '
                            .($i->treatment->name ?? 'Tratamiento')
                            .' · $'.number_format((float) $i->subtotal, 2),
                    ]))
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => static::compute($get, $set)),

            Select::make('commission_type')
                ->label('Tipo')
                ->options(['percentage' => 'Porcentaje', 'fixed' => 'Monto fijo'])
                ->default('percentage')->required()->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => static::compute($get, $set)),

            TextInput::make('commission_value')
                ->label('Valor')
                ->numeric()->minValue(0)->required()
                ->suffix(fn (Get $get) => $get('commission_type') === 'percentage' ? '%' : '$')
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Get $get, Set $set) => static::compute($get, $set)),

            TextInput::make('calculated_amount')
                ->label('Monto calculado')
                ->numeric()->prefix('$')->readOnly(),

            TextInput::make('period')
                ->label('Período (AAAA-MM)')
                ->default(now()->format('Y-m'))
                ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                ->maxLength(10)
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['dentist', 'treatmentPlanItem.treatment']))
            ->columns([
                TextColumn::make('period')->label('Período')->sortable()->searchable(),

                TextColumn::make('dentist.name')->label('Odontólogo')->searchable(),

                TextColumn::make('treatmentPlanItem.treatment.name')->label('Tratamiento')->limit(30),

                TextColumn::make('commission_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Porcentaje' : 'Fijo'),

                TextColumn::make('commission_value')->label('Valor'),

                TextColumn::make('calculated_amount')
                    ->label('Monto')
                    ->money('usd')
                    ->summarize(Sum::make()->label('Total')->money('usd')),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'paid' ? 'Pagada' : 'Pendiente')
                    ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('user_id')->label('Odontólogo')
                    ->options(fn () => static::dentists()->pluck('name', 'id')),
                SelectFilter::make('status')->label('Estado')
                    ->options(['pending' => 'Pendiente', 'paid' => 'Pagada']),
                SelectFilter::make('period')->label('Período')
                    ->options(fn () => DentistCommission::query()->distinct()->orderByDesc('period')->pluck('period', 'period')),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->visible(fn (DentistCommission $record) => $record->status === 'pending'),
            ])
            ->toolbarActions([
                BulkAction::make('marcar_pagadas')
                    ->label('Marcar como pagadas (liquidar)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(fn (Collection $records) => $records
                        ->where('status', 'pending')
                        ->each->update(['status' => 'paid'])),
            ])
            ->defaultSort('period', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDentistCommissions::route('/'),
            'create' => Pages\CreateDentistCommission::route('/create'),
            'edit'   => Pages\EditDentistCommission::route('/{record}/edit'),
        ];
    }
}
