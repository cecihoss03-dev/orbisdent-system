<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model implements HasAvatar, HasName
{
    use SoftDeletes;

    protected $fillable = [
        'plan_id',
        'business_name',
        'tax_id',
        'address',
        'phone',
        'logo_url',
        'status',
        'timezone',
        'currency',
        'cancellation_policy',
        'settings',
    ];

    protected $casts = [
        'cancellation_policy' => 'array',
        'settings'            => 'array',
    ];

    /**
     * Filament muestra el nombre de la clínica en el selector de tenant.
     * Sin esto busca una columna "name" que esta tabla no tiene
     * (la nuestra es business_name) y el panel falla.
     */
    public function getFilamentName(): string
    {
        return $this->business_name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo_url ?: null;
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function odontograms(): HasMany
    {
        return $this->hasMany(Odontogram::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
