<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PaymentResource\Pages;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Support\Billing\InvoiceBalance;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación';

    protected static ?string $modelLabel = 'pago';

    protected static ?string $pluralModelLabel = 'pagos';

    protected static ?int $navigationSort = 2;

    public const STATUS_LABELS = [
        'completed' => 'Completado',
        'refunded'  => 'Reembolsado',
        'voided'    => 'Anulado',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('patient_id')
                ->label('Paciente')
                ->relationship('patient', 'first_name')
                ->getOptionLabelFromRecordUsing(fn (Patient $record) => $record->full_name)
                ->searchable(['first_name', 'last_name'])
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('invoice_id', null))
                ->disabledOn('edit'),

            Select::make('invoice_id')
                ->label('Factura (opcional)')
                ->options(fn (Get $get) => $get('patient_id')
                    ? Invoice::where('patient_id', $get('patient_id'))
                        ->whereIn('status', ['issued', 'partially_paid'])
                        ->get()
                        ->mapWithKeys(fn (Invoice $i) => [
                            $i->id => "{$i->invoice_number} — saldo $".number_format(InvoiceBalance::balance($i), 2),
                        ])
                    : [])
                ->searchable()
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ($invoice = $state ? Invoice::find($state) : null) {
                        $set('amount', InvoiceBalance::balance($invoice));
                        $set('treatment_plan_id', $invoice->treatment_plan_id);
                    }
                })
                ->disabledOn('edit'),

            Hidden::make('treatment_plan_id'),

            TextInput::make('amount')
                ->label('Monto')
                ->numeric()->prefix('$')->required()->minValue(0.01)
                ->rules([
                    fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if ($get('invoice_id') && $invoice = Invoice::find($get('invoice_id'))) {
                            if ((float) $value > InvoiceBalance::balance($invoice) + 0.001) {
                                $fail('El monto supera el saldo pendiente de la factura.');
                            }
                        }
                    },
                ])
                ->disabledOn('edit'),

            Select::make('payment_method')
                ->label('Método de pago')
                ->options(Payment::METHODS)
                ->default('cash')->required(),

            DatePicker::make('payment_date')->label('Fecha')->default(now())->required(),

            TextInput::make('reference_number')->label('Referencia / N.º de operación')->maxLength(60),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['patient', 'invoice', 'receivedBy']))
            ->columns([
                TextColumn::make('payment_date')->label('Fecha')->date('d/m/Y')->sortable(),

                TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(['patients.first_name', 'patients.last_name']),

                TextColumn::make('invoice.invoice_number')->label('Factura')->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('usd')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('usd')),

                TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Payment::METHODS[$state] ?? $state),

                TextColumn::make('receivedBy.name')->label('Recibió')->toggleable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::STATUS_LABELS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'refunded'  => 'warning',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('payment_method')->label('Método')->options(Payment::METHODS),
                SelectFilter::make('status')->label('Estado')->options(self::STATUS_LABELS),
                Filter::make('hoy')
                    ->label('Hoy')
                    ->query(fn ($query) => $query->whereDate('payment_date', today())),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),

                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record) => $record->status === 'completed' && Gate::allows('update', $record))
                    ->action(fn (Payment $record) => $record->update(['status' => 'voided'])),

                Action::make('reembolsar')
                    ->label('Reembolsar')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record) => $record->status === 'completed' && Gate::allows('update', $record))
                    ->action(fn (Payment $record) => $record->update(['status' => 'refunded'])),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    /** Un pago no se borra: se anula o reembolsa (el model hook re-sincroniza la factura). */
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return parent::canEdit($record) && $record->status === 'completed';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
