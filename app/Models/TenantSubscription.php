<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    const UPDATED_AT = null; // la tabla solo registra created_at

    // Nota: a propósito NO usa el trait BelongsToTenant. Esta tabla la
    // administra el Admin General del SaaS (facturación de suscripciones),
    // no el personal de la clínica — no debe filtrarse por el tenant
    // "actual" de la sesión.

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'start_date',
        'end_date',
        'status', // active, expired, cancelled
        'amount_paid',
        'payment_method',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
