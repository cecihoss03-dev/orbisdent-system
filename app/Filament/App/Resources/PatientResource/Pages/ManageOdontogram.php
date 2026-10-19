<?php

namespace App\Filament\App\Resources\PatientResource\Pages;

use App\Filament\App\Resources\PatientResource;
use App\Models\Patient;
use App\Models\Odontogram;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Gate;

class ManageOdontogram extends Page
{
    protected static string $resource = PatientResource::class;

    protected string $view = 'filament.app.resources.patient-resource.pages.manage-odontogram';

    protected static ?string $title = 'Odontograma';

    public Patient $record;

    public function mount(int|string $record): void
    {
        abort_unless(Gate::allows('viewAny', Odontogram::class), 403);

        $this->record = Patient::findOrFail($record);
    }

    public function getBreadcrumb(): string
    {
        return 'Odontograma';
    }
}
