<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentistSchedule extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'chair_id',
        'day_of_week', // 0=domingo..6=sábado
        'start_time',
        'end_time',
    ];

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function chair(): BelongsTo
    {
        return $this->belongsTo(Chair::class);
    }

    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }
}
