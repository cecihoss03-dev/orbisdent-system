<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalHistory extends Model
{
    const CREATED_AT = null;

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'patient_id',
        'blood_type',
        'allergies',
        'systemic_diseases',
        'current_medications',
        'pregnancy_status',
        'is_smoker',
        'alcohol_consumption',
        'other_notes',
        'updated_by',
    ];

    protected $casts = [
        'allergies'            => 'array',
        'systemic_diseases'    => 'array',
        'current_medications'  => 'array',
        'is_smoker'            => 'boolean',
        'updated_at'           => 'datetime',
    ];

    protected static function booted(): void
    {
        // Antes de sobrescribir el registro vigente, guarda una copia
        // completa del estado anterior en el histórico de auditoría.
        static::updating(function (PatientMedicalHistory $history) {
            PatientMedicalHistoryLog::create([
                'patient_id' => $history->patient_id,
                'snapshot'   => $history->getOriginal(),
                'changed_by' => $history->updated_by,
            ]);
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
