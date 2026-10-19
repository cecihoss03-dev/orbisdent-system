<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Filament\Admin\Resources\PlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Un plan con historial de suscripciones no se elimina
            // (la base de datos lo protege); se desactiva con "Activo".
            DeleteAction::make()
                ->visible(fn () => $this->record->subscriptions()->doesntExist()),
        ];
    }
}
