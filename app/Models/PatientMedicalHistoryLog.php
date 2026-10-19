<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalHistoryLog extends Model
{
    const CREATED_AT = 'changed_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'patient_id',
        'snapshot',
        'changed_by',
    ];

    protected $casts = [
        'snapshot'   => 'array',
        'changed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
