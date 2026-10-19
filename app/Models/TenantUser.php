<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends Model
{
    use BelongsToTenant;

    const CREATED_AT = 'joined_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role_id',
        'status', // active, invited, disabled
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
