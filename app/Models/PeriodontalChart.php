<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodontalChart extends Model
{
    use BelongsToTenant;

    public $timestamps = false; // usa recorded_at propio

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'appointment_id',
        'recorded_by',
        'recorded_at',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(PeriodontalMeasurement::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
