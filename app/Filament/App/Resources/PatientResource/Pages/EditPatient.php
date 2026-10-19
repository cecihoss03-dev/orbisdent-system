<?php

namespace App\Filament\App\Resources\PatientResource\Pages;

use App\Filament\App\Resources\PatientResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPatient extends EditRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('odontogram')
                ->label('Ver odontograma')
                ->icon('heroicon-o-face-smile')
                ->color('success')
                ->url(fn () => PatientResource::getUrl('odontogram', ['record' => $this->record])),

            DeleteAction::make(),
        ];
    }
}
