<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlanInstallment extends Model
{
    public $timestamps = false;

    public const STATUSES = ['pending', 'paid', 'overdue'];

    protected $fillable = [
        'treatment_plan_id',
        'due_date',
        'amount',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount'   => 'decimal:2',
    ];

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class);
    }
}
