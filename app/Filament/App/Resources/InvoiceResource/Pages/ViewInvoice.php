<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use App\Models\Payment;
use App\Support\Billing\InvoiceBalance;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrar_pago')
                ->label('Registrar pago')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['issued', 'partially_paid'], true) && Gate::allows('create', Payment::class))
                ->schema(fn () => [
                    TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()->prefix('$')->required()
                        ->minValue(0.01)
                        ->maxValue(InvoiceBalance::balance($this->record))
                        ->default(InvoiceBalance::balance($this->record))
                        ->helperText('Saldo pendiente: $'.number_format(InvoiceBalance::balance($this->record), 2)),

                    Select::make('payment_method')
                        ->label('Método')
                        ->options(Payment::METHODS)
                        ->default('cash')->required(),

                    DatePicker::make('payment_date')->label('Fecha')->default(now())->required(),

                    TextInput::make('reference_number')->label('Referencia / N.º de operación')->maxLength(60),
                ])
                ->action(function (array $data) {
                    Payment::create($data + [
                        'patient_id'        => $this->record->patient_id,
                        'invoice_id'        => $this->record->id,
                        'treatment_plan_id' => $this->record->treatment_plan_id,
                        'received_by'       => Auth::id(),
                        'status'            => 'completed',
                    ]);

                    $this->record->refresh();

                    Notification::make()->title('Pago registrado')->success()->send();
                }),

            EditAction::make()
                ->visible(fn () => InvoiceResource::canEdit($this->record)),

            Action::make('anular')
                ->label('Anular factura')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('La factura quedará anulada y no podrá recibir pagos. Esta acción no se puede deshacer.')
                ->visible(fn () => $this->record->status !== 'void' && Gate::allows('update', $this->record))
                ->action(function () {
                    if (InvoiceBalance::paid($this->record) > 0) {
                        Notification::make()
                            ->title('No se puede anular')
                            ->body('La factura tiene pagos completados. Anula o reembolsa los pagos primero.')
                            ->danger()->send();

                        return;
                    }

                    $this->record->update(['status' => 'void']);
                    Notification::make()->title('Factura anulada')->success()->send();
                }),
        ];
    }
}
