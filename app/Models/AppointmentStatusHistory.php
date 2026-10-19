<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentStatusHistory extends Model
{
    const CREATED_AT = 'changed_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'appointment_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
