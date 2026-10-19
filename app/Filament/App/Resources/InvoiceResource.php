<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Support\Billing\InvoiceBalance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación';

    protected static ?string $modelLabel = 'factura';

    protected static ?string $pluralModelLabel = 'facturas';

    protected static ?int $navigationSort = 1;

    public const STATUS_LABELS = [
        'issued'         => 'Emitida',
        'partially_paid' => 'Pago parcial',
        'paid'           => 'Pagada',
        'void'           => 'Anulada',
    ];

    public const STATUS_COLORS = [
        'issued'         => 'warning',
        'partially_paid' => 'info',
        'paid'           => 'success',
        'void'           => 'gray',
    ];

    /** Recalcula subtotal/total a partir de los ítems y el impuesto. */
    public static function recalculate(Get $get, Set $set): void
    {
        $subtotal = collect($get('items') ?? [])->sum(
            fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)
        );

        $set('subtotal', round($subtotal, 2));
        $set('total', round($subtotal + (float) ($get('tax') ?? 0), 2));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de la factura')
                ->columns(2)
                ->schema([
                    Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('treatment_plan_id', null)),

                    Select::make('treatment_plan_id')
                        ->label('Plan de tratamiento (opcional)')
                        ->options(fn (Get $get) => $get('patient_id')
                            ? TreatmentPlan::where('patient_id', $get('patient_id'))
                                ->whereIn('status', ['approved', 'in_progress', 'completed'])
                                ->get()
                                ->mapWithKeys(fn ($p) => [$p->id => "Plan #{$p->id} — $".number_format((float) $p->total_amount, 2)])
                            : [])
                        ->searchable()
                        ->live()
                        ->helperText('Al elegirlo se cargan sus tratamientos como ítems de la factura.')
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if (! $state) {
                                return;
                            }
                            $plan = TreatmentPlan::with('items.treatment')->find($state);
                            if (! $plan) {
                                return;
                            }
                            $items = $plan->items
                                ->where('status', '!=', 'cancelled')
                                ->map(fn ($i) => [
                                    'description' => trim(($i->treatment->name ?? 'Tratamiento')
                                        .($i->tooth_fdi_code ? " (diente {$i->tooth_fdi_code})" : '')),
                                    'quantity'   => $i->quantity,
                                    'unit_price' => round(((float) $i->subtotal) / max(1, (int) $i->quantity), 2),
                                    'subtotal'   => (float) $i->subtotal,
                                ])->values()->all();

                            $set('items', $items);
                            static::recalculate($get, $set);
                        }),

                    TextInput::make('invoice_number')
                        ->label('N.º de factura')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Se asigna al guardar'),

                    DatePicker::make('issue_date')
                        ->label('Fecha de emisión')
                        ->default(now())
                        ->required(),
                ]),

            Section::make('Detalle')
                ->schema([
                    Repeater::make('items')
                        ->label('Ítems')
                        ->relationship('items')
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::recalculate($get, $set))
                        ->columns(12)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->schema([
                            TextInput::make('description')
                                ->label('Descripción')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(6),

                            TextInput::make('quantity')
                                ->label('Cant.')
                                ->numeric()->minValue(1)->default(1)->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                    'subtotal',
                                    round((float) $get('quantity') * (float) $get('unit_price'), 2)
                                ))
                                ->columnSpan(2),

                            TextInput::make('unit_price')
                                ->label('P. unitario')
                                ->numeric()->minValue(0)->required()->prefix('$')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                    'subtotal',
                                    round((float) $get('quantity') * (float) $get('unit_price'), 2)
                                ))
                                ->columnSpan(2),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()->prefix('$')->readOnly()->default(0)
                                ->dehydrated()
                                ->columnSpan(2),
                        ]),
                ]),

            Section::make('Totales')
                ->columns(3)
                ->schema([
                    TextInput::make('subtotal')->label('Subtotal')
                        ->numeric()->prefix('$')->readOnly()->default(0),

                    TextInput::make('tax')->label('Impuesto')
                        ->numeric()->prefix('$')->default(0)->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => static::recalculate($get, $set)),

                    TextInput::make('total')->label('Total')
                        ->numeric()->prefix('$')->readOnly()->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('patient')->withSum(
                ['payments as paid_sum' => fn ($p) => $p->where('status', 'completed')],
                'amount'
            ))
            ->columns([
                TextColumn::make('invoice_number')->label('N.º')->searchable()->sortable(),

                TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(['patients.first_name', 'patients.last_name']),

                TextColumn::make('issue_date')->label('Emisión')->date('d/m/Y')->sortable(),

                TextColumn::make('total')->label('Total')->money('usd')->sortable(),

                TextColumn::make('paid_sum')->label('Pagado')->money('usd')->default(0),

                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('usd')
                    ->state(fn (Invoice $record) => $record->status === 'void'
                        ? 0
                        : max(0, (float) $record->total - (float) ($record->paid_sum ?? 0))),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::STATUS_LABELS[$state] ?? $state)
                    ->color(fn ($state) => self::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(self::STATUS_LABELS),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->defaultSort('issue_date', 'desc');
    }

    /** Solo se edita una factura vigente y sin pagos. */
    public static function canEdit($record): bool
    {
        return parent::canEdit($record) && $record->status === 'issued' && ! $record->payments()->exists();
    }

    /** Las facturas no se borran: se anulan (trazabilidad contable). */
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view'   => Pages\ViewInvoice::route('/{record}'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
