<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientEmergencyContact extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'name',
        'relationship',
        'phone',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
