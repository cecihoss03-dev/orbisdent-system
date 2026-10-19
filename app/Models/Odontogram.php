<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odontogram extends Model
{
    use BelongsToTenant;

    public $timestamps = false; // usa recorded_at como marca temporal propia

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'appointment_id',
        'dentition_type',
        'is_current',
        'recorded_by',
        'recorded_at',
        'general_notes',
    ];

    protected $casts = [
        'is_current'  => 'boolean',
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

    public function toothStates(): HasMany
    {
        return $this->hasMany(OdontogramToothState::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OdontogramAttachment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Marca este odontograma como el vigente del paciente y desmarca
     * cualquier otro (coherente con el índice único parcial is_current
     * definido en el DDL).
     */
    public function markAsCurrent(): void
    {
        static::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        $this->update(['is_current' => true]);
    }
}
