<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentForm extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'treatment_plan_id',
        'form_type',
        'signed_file_url',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function scopeSigned($query)
    {
        return $query->whereNotNull('signed_at');
    }
}
