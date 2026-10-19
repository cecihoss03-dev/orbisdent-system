<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramAttachment extends Model
{
    use BelongsToTenant;

    const CREATED_AT = 'uploaded_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'odontogram_id',
        'tooth_treatment_event_id',
        'type', // xray, intraoral_photo, panoramic, document
        'file_url',
        'uploaded_by',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }

    public function toothTreatmentEvent(): BelongsTo
    {
        return $this->belongsTo(ToothTreatmentEvent::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
