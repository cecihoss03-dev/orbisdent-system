<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleException extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'date',
        'type', // vacation, block, extra_hours
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
