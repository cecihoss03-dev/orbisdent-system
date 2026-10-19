<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * Estados válidos (coinciden con el CHECK constraint del DDL).
     */
    public const STATUSES = [
        'scheduled', 'confirmed', 'waiting', 'in_progress',
        'completed', 'cancelled', 'no_show',
    ];

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'dentist_id',
        'chair_id',
        'treatment_id',
        'scheduled_start',
        'scheduled_end',
        'status',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end'   => 'datetime',
    ];

    protected static function booted(): void
    {
        // Cada cambio de estado queda registrado automáticamente en
        // appointment_status_history, sin depender de que el controller
        // se acuerde de auditarlo.
        static::updating(function (Appointment $appointment) {
            if ($appointment->isDirty('status')) {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'old_status'     => $appointment->getOriginal('status'),
                    'new_status'     => $appointment->status,
                    'changed_by'     => auth()->id(),
                ]);
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }

    public function chair(): BelongsTo
    {
        return $this->belongsTo(Chair::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    public function odontograms(): HasMany
    {
        return $this->hasMany(Odontogram::class);
    }

    public function toothTreatmentEvents(): HasMany
    {
        return $this->hasMany(ToothTreatmentEvent::class);
    }

    public function periodontalCharts(): HasMany
    {
        return $this->hasMany(PeriodontalChart::class);
    }

    public function clinicalEvolutionNotes(): HasMany
    {
        return $this->hasMany(ClinicalEvolutionNote::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentCatalog::class, 'treatment_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_start', '>=', now())
            ->whereNotIn('status', ['cancelled', 'completed', 'no_show']);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('scheduled_start', $date);
    }
}
