<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\TreatmentPlanResource\Pages;
use App\Models\Patient;
use App\Models\ToothDefinition;
use App\Models\ToothSurfaceCatalog;
use App\Models\TreatmentCatalog;
use App\Models\TreatmentPlan;
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
use Illuminate\Support\Facades\Auth;

class TreatmentPlanResource extends Resource
{
    protected static ?string $model = TreatmentPlan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Tratamientos';

    protected static ?string $modelLabel = 'presupuesto';

    protected static ?string $pluralModelLabel = 'presupuestos';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('patient_id')
                ->label('Paciente')
                ->relationship('patient', 'first_name')
                ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                ->searchable(['first_name', 'last_name'])
                ->preload()
                ->required(),

            Select::make('status')
                ->label('Estado')
                ->options([
                    'draft'       => 'Borrador',
                    'approved'    => 'Aprobado',
                    'rejected'    => 'Rechazado',
                    'in_progress' => 'En ejecución',
                    'completed'   => 'Finalizado',
                ])
                ->required()
                ->default('draft'),

            Repeater::make('items')
                ->relationship()
                ->label('Ítems del presupuesto')
                ->schema([
                    Select::make('treatment_id')
                        ->label('Tratamiento')
                        ->options(fn () => TreatmentCatalog::query()->active()->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $set('unit_price', TreatmentCatalog::find($state)?->base_price ?? 0);
                        })
                        ->required(),

                    Select::make('tooth_fdi_code')
                        ->label('Diente')
                        ->options(fn () => ToothDefinition::query()->ordered()->pluck('name', 'fdi_code'))
                        ->searchable(),

                    Select::make('surface_code')
                        ->label('Cara')
                        ->options(fn () => ToothSurfaceCatalog::query()->pluck('name', 'code')),

                    TextInput::make('quantity')
                        ->label('Cant.')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemSubtotal($get, $set)),

                    TextInput::make('unit_price')
                        ->label('Precio unit.')
                        ->numeric()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemSubtotal($get, $set)),

                    TextInput::make('discount')
                        ->label('Descuento')
                        ->numeric()
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateItemSubtotal($get, $set)),

                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->readOnly(),

                    Select::make('status')
                        ->label('Estado del ítem')
                        ->options([
                            'pending'     => 'Pendiente',
                            'in_progress' => 'En ejecución',
                            'completed'   => 'Completado',
                            'cancelled'   => 'Cancelado',
                        ])
                        ->default('pending')
                        ->required(),
                ])
                ->columns(4)
                ->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculatePlanTotal($get, $set))
                ->deleteAction(
                    fn ($action) => $action->after(fn (Get $get, Set $set) => self::recalculatePlanTotal($get, $set))
                )
                ->addActionLabel('Agregar ítem')
                ->columnSpanFull(),

            Section::make('Totales')
                ->schema([
                    TextInput::make('discount_amount')
                        ->label('Descuento general')
                        ->numeric()
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculatePlanTotal($get, $set)),

                    TextInput::make('total_amount')
                        ->label('Total del presupuesto')
                        ->numeric()
                        ->prefix('$')
                        ->readOnly()
                        ->default(0),
                ])
                ->columns(2),

            Repeater::make('installments')
                ->relationship()
                ->label('Cuotas')
                ->schema([
                    DatePicker::make('due_date')
                        ->label('Vencimiento')
                        ->required()
                        ->native(false),

                    TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->required(),

                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'paid'    => 'Pagada',
                            'overdue' => 'Vencida',
                        ])
                        ->default('pending')
                        ->required(),
                ])
                ->columns(3)
                ->addActionLabel('Agregar cuota')
                ->columnSpanFull(),
        ]);
    }

    protected static function recalculateItemSubtotal(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $discount = (float) ($get('discount') ?? 0);

        $set('subtotal', max(0, ($quantity * $unitPrice) - $discount));
    }

    protected static function recalculatePlanTotal(Get $get, Set $set): void
    {
        $itemsTotal = collect($get('items') ?? [])->sum('subtotal');
        $discountAmount = (float) ($get('discount_amount') ?? 0);

        $set('total_amount', max(0, $itemsTotal - $discountAmount));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(['patients.first_name', 'patients.last_name']),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'       => 'gray',
                        'approved'    => 'info',
                        'in_progress' => 'warning',
                        'completed'   => 'success',
                        'rejected'    => 'danger',
                        default       => 'gray',
                    }),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('usd')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Ítems')
                    ->counts('items'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft'       => 'Borrador',
                        'approved'    => 'Aprobado',
                        'rejected'    => 'Rechazado',
                        'in_progress' => 'En ejecución',
                        'completed'   => 'Finalizado',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTreatmentPlans::route('/'),
            'create' => Pages\CreateTreatmentPlan::route('/create'),
            'edit'   => Pages\EditTreatmentPlan::route('/{record}/edit'),
        ];
    }
}
