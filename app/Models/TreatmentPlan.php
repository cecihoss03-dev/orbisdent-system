<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPlan extends Model
{
    use BelongsToTenant;

    public const STATUSES = ['draft', 'approved', 'rejected', 'in_progress', 'completed'];

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'created_by',
        'status',
        'total_amount',
        'discount_amount',
        'approved_at',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'approved_at'     => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TreatmentPlanItem::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(TreatmentPlanInstallment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function consentForms(): HasMany
    {
        return $this->hasMany(ConsentForm::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
