<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToothTreatmentEvent extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // la tabla solo registra created_at

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'tooth_fdi_code',
        'surface_code',
        'condition_type_id',
        'dentist_id',
        'appointment_id',
        'odontogram_before_id',
        'odontogram_after_id',
        'performed_at',
        'notes',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(ToothDefinition::class, 'tooth_fdi_code', 'fdi_code');
    }

    public function surface(): BelongsTo
    {
        return $this->belongsTo(ToothSurfaceCatalog::class, 'surface_code', 'code');
    }

    public function conditionType(): BelongsTo
    {
        return $this->belongsTo(ConditionTypeCatalog::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function odontogramBefore(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class, 'odontogram_before_id');
    }

    public function odontogramAfter(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class, 'odontogram_after_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OdontogramAttachment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
