<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chair extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'status', // available, occupied, maintenance
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dentistSchedules(): HasMany
    {
        return $this->hasMany(DentistSchedule::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
