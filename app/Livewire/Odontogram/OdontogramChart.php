<?php

namespace App\Livewire\Odontogram;

use App\Models\ConditionTypeCatalog;
use App\Models\Odontogram;
use App\Models\OdontogramAttachment;
use App\Models\OdontogramToothState;
use App\Models\Patient;
use App\Models\ToothDefinition;
use App\Models\ToothTreatmentEvent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Carta dental interactiva: dibuja los dientes del odontograma que se
 * esté viendo (vigente o una versión histórica), cada uno dividido en 5
 * regiones clickeables. Solo la versión vigente admite edición.
 */
class OdontogramChart extends Component implements HasForms
{
    use InteractsWithForms;

    public Patient $patient;

    public ?Odontogram $odontogram = null;

    public ?string $selectedTooth = null;

    public ?string $selectedSurface = null; // null = pieza completa

    public ?array $data = [];

    public ?array $attachmentData = [];

    public bool $needsSetup = false;

    public string $dentitionType = 'adult';

    public function mount(Patient $patient): void
    {
        abort_unless(Gate::allows('viewAny', Odontogram::class), 403);

        $this->patient = $patient;
        $this->odontogram = $patient->odontograms()->where('is_current', true)->first();

        if (! $this->odontogram) {
            $this->needsSetup = true;
            $this->dentitionType = $this->inferDentitionType();
        } else {
            $this->dentitionType = $this->odontogram->dentition_type;
        }

        $this->form->fill();
        $this->attachmentForm->fill();
    }

    protected function inferDentitionType(): string
    {
        $age = $this->patient->birth_date?->age;

        return ($age !== null && $age < 13) ? 'pediatric' : 'adult';
    }

    public function createInitialOdontogram(): void
    {
        abort_unless(Gate::allows('create', Odontogram::class), 403);
        $this->odontogram = Odontogram::create([
            'patient_id'     => $this->patient->id,
            'dentition_type' => $this->dentitionType,
            'is_current'     => true,
            'recorded_by'    => Auth::id(),
            'recorded_at'    => now(),
        ]);

        $this->needsSetup = false;
    }

    public function updateDentitionType(): void
    {
        abort_unless(Gate::allows('update', new Odontogram()), 403);
        if (! $this->odontogram || ! $this->odontogram->is_current) {
            return;
        }

        $this->odontogram->update(['dentition_type' => $this->dentitionType]);

        Notification::make()->title('Tipo de dentición actualizado')->success()->send();
    }

    protected function getForms(): array
    {
        return ['form', 'attachmentForm'];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('condition_type_id')
                    ->label('Condición')
                    ->options(ConditionTypeCatalog::query()->pluck('name', 'id'))
                    ->required()
                    ->native(false),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(2),
            ])
            ->statePath('data');
    }

    public function attachmentForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'xray'            => 'Radiografía',
                        'intraoral_photo' => 'Foto intraoral',
                        'panoramic'       => 'Panorámica',
                        'document'        => 'Documento',
                    ])
                    ->required()
                    ->native(false),

                Select::make('tooth_treatment_event_id')
                    ->label('Diente relacionado (opcional)')
                    ->helperText('Deja vacío si es un adjunto general de esta consulta, no de una pieza específica.')
                    ->options(fn () => ToothTreatmentEvent::query()
                        ->where('odontogram_after_id', $this->odontogram?->id)
                        ->get()
                        ->mapWithKeys(fn (ToothTreatmentEvent $e) => [
                            $e->id => "Diente {$e->tooth_fdi_code}".($e->surface_code ? " ({$e->surface_code})" : '').' — '.$e->performed_at->format('d/m/Y'),
                        ]))
                    ->native(false),

                FileUpload::make('file_url')
                    ->label('Archivo')
                    ->directory('odontogram-attachments')
                    ->required(),
            ])
            ->statePath('attachmentData');
    }

    public function getToothChartDataProperty(): array
    {
        if (! $this->odontogram) {
            return [];
        }

        $teeth = ToothDefinition::query()
            ->where('dentition_type', $this->odontogram->dentition_type)
            ->ordered()
            ->get();

        $states = $this->odontogram->toothStates()
            ->with('conditionType')
            ->get()
            ->groupBy('tooth_fdi_code');

        return $teeth->map(function (ToothDefinition $tooth) use ($states) {
            $regionColors = [];
            $wholeColor = null;

            foreach ($states->get($tooth->fdi_code, collect()) as $state) {
                $color = $state->color_override ?: $state->conditionType->default_color;

                if ($state->surface_code === null) {
                    $wholeColor = $color;
                } else {
                    $regionColors[$state->surface_code] = $color;
                }
            }

            return [
                'fdi_code'      => $tooth->fdi_code,
                'name'          => $tooth->name,
                'mesial_side'   => in_array($tooth->quadrant, [1, 4]) ? 'right' : 'left',
                'region_colors' => $regionColors,
                'whole_color'   => $wholeColor,
            ];
        })->values()->toArray();
    }

    public function getVersionsProperty()
    {
        return $this->patient->odontograms()->orderBy('recorded_at', 'desc')->get();
    }

    public function getIsCurrentVersionProperty(): bool
    {
        return (bool) $this->odontogram?->is_current;
    }

    public function getAttachmentsProperty()
    {
        if (! $this->odontogram) {
            return collect();
        }

        return OdontogramAttachment::query()
            ->where(function ($q) {
                $q->where('odontogram_id', $this->odontogram->id)
                    ->orWhereIn('tooth_treatment_event_id', $this->odontogram->toothTreatmentEvents()->pluck('id'));
            })
            ->latest('uploaded_at')
            ->get();
    }

    public function switchVersion(int $odontogramId): void
    {
        $this->odontogram = $this->patient->odontograms()->findOrFail($odontogramId);
        $this->dentitionType = $this->odontogram->dentition_type;
    }

    public function selectRegion(string $toothCode, ?string $surfaceCode): void
    {
        if (! $this->isCurrentVersion) {
            Notification::make()
                ->title('Esta es una versión histórica, de solo lectura')
                ->body('Vuelve a la versión vigente para editar, o crea una nueva versión a partir de esta.')
                ->warning()
                ->send();

            return;
        }

        $this->selectedTooth = $toothCode;
        $this->selectedSurface = $surfaceCode;

        $existing = $this->odontogram->toothStates()
            ->where('tooth_fdi_code', $toothCode)
            ->where('surface_code', $surfaceCode)
            ->first();

        $this->form->fill([
            'condition_type_id' => $existing?->condition_type_id,
            'notes'             => $existing?->notes,
        ]);

        $this->dispatch('open-tooth-modal');
    }

    public function saveCondition(): void
    {
        abort_unless(Gate::allows('update', new Odontogram()), 403);
        if (! $this->isCurrentVersion) {
            return;
        }

        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            OdontogramToothState::updateOrCreate(
                [
                    'odontogram_id'  => $this->odontogram->id,
                    'tooth_fdi_code' => $this->selectedTooth,
                    'surface_code'   => $this->selectedSurface,
                ],
                [
                    'condition_type_id' => $data['condition_type_id'],
                    'notes'             => $data['notes'] ?? null,
                ]
            );

            ToothTreatmentEvent::create([
                'patient_id'          => $this->patient->id,
                'tooth_fdi_code'      => $this->selectedTooth,
                'surface_code'        => $this->selectedSurface,
                'condition_type_id'   => $data['condition_type_id'],
                'dentist_id'          => Auth::id(),
                'odontogram_after_id' => $this->odontogram->id,
                'performed_at'        => now(),
                'notes'               => $data['notes'] ?? null,
            ]);
        });

        $this->dispatch('close-tooth-modal');
        $this->reset(['selectedTooth', 'selectedSurface']);

        Notification::make()->title('Odontograma actualizado')->success()->send();
    }

    public function saveAttachment(): void
    {
        abort_unless(Gate::allows('update', new Odontogram()), 403);
        $data = $this->attachmentForm->getState();

        OdontogramAttachment::create([
            'patient_id'               => $this->patient->id,
            'odontogram_id'            => $data['tooth_treatment_event_id'] ? null : $this->odontogram->id,
            'tooth_treatment_event_id' => $data['tooth_treatment_event_id'] ?? null,
            'type'                     => $data['type'],
            'file_url'                 => $data['file_url'],
            'uploaded_by'              => Auth::id(),
        ]);

        $this->attachmentForm->fill();

        Notification::make()->title('Adjunto guardado')->success()->send();
    }

    public function startNewVersion(): void
    {
        abort_unless(Gate::allows('create', Odontogram::class), 403);
        DB::transaction(function () {
            $new = Odontogram::create([
                'patient_id'     => $this->patient->id,
                'dentition_type' => $this->odontogram->dentition_type,
                'is_current'     => false,
                'recorded_by'    => Auth::id(),
                'recorded_at'    => now(),
            ]);

            foreach ($this->odontogram->toothStates as $state) {
                $new->toothStates()->create([
                    'tooth_fdi_code'    => $state->tooth_fdi_code,
                    'surface_code'      => $state->surface_code,
                    'condition_type_id' => $state->condition_type_id,
                    'notes'             => $state->notes,
                    'color_override'    => $state->color_override,
                ]);
            }

            $new->markAsCurrent();
            $this->odontogram = $new->fresh();
            $this->dentitionType = $this->odontogram->dentition_type;
        });

        Notification::make()->title('Se abrió una nueva versión del odontograma')->success()->send();
    }

    public function render()
    {
        return view('livewire.odontogram.odontogram-chart', [
            'teeth' => $this->toothChartData,
        ]);
    }
}
