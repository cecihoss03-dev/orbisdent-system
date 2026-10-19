<?php

namespace App\Filament\App\Resources\AppointmentResource\Pages;

use App\Filament\App\Resources\AppointmentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('statusHistory')
                ->label('Historial de estados')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->modalHeading('Historial de cambios de estado')
                ->modalContent(fn () => view(
                    'filament.app.resources.appointment-resource.partials.status-history',
                    ['histories' => $this->record->statusHistory()->with('changedBy')->latest('changed_at')->get()]
                ))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),

            DeleteAction::make(),
        ];
    }
}
