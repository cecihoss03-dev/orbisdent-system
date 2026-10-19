<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'birth_date',
        'gender',
        'address',
        'phone',
        'email',
        'occupation',
        'marital_status',
        'referred_by',
        'profile_photo_url',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(PatientEmergencyContact::class);
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(PatientMedicalHistory::class);
    }

    public function medicalHistoryLogs(): HasMany
    {
        return $this->hasMany(PatientMedicalHistoryLog::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function odontograms(): HasMany
    {
        return $this->hasMany(Odontogram::class);
    }

    public function currentOdontogram(): HasOne
    {
        return $this->hasOne(Odontogram::class)->where('is_current', true);
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

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /*
     * Relaciones hacia TreatmentPlan, Invoice, Payment se agregan cuando
     * esos modelos se generen en el siguiente paso.
     */
}
