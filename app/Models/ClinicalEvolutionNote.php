<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalEvolutionNote extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'appointment_id',
        'dentist_id',
        'visit_date',
        'reason',
        'diagnosis_cie10',
        'subjective',
        'objective',
        'assessment',
        'plan',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Cie10DentalCatalog::class, 'diagnosis_cie10', 'code');
    }

    /*
     * appointment() se agrega cuando se genere el modelo Appointment.
     */

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
