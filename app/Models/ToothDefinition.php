<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToothDefinition extends Model
{
    protected $primaryKey = 'fdi_code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'fdi_code',
        'dentition_type',
        'quadrant',
        'name',
        'display_order',
    ];

    public function toothStates(): HasMany
    {
        return $this->hasMany(OdontogramToothState::class, 'tooth_fdi_code', 'fdi_code');
    }

    public function treatmentEvents(): HasMany
    {
        return $this->hasMany(ToothTreatmentEvent::class, 'tooth_fdi_code', 'fdi_code');
    }

    public function periodontalMeasurements(): HasMany
    {
        return $this->hasMany(PeriodontalMeasurement::class, 'tooth_fdi_code', 'fdi_code');
    }

    public function scopeAdult($query)
    {
        return $query->where('dentition_type', 'adult');
    }

    public function scopePediatric($query)
    {
        return $query->where('dentition_type', 'pediatric');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}