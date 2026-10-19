<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_url',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function dentistProfile(): HasOne
    {
        return $this->hasOne(DentistProfile::class);
    }

    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function odontogramsRecorded(): HasMany
    {
        return $this->hasMany(Odontogram::class, 'recorded_by');
    }

    public function toothTreatmentEvents(): HasMany
    {
        return $this->hasMany(ToothTreatmentEvent::class, 'dentist_id');
    }

    public function clinicalEvolutionNotes(): HasMany
    {
        return $this->hasMany(ClinicalEvolutionNote::class, 'dentist_id');
    }

    /**
     * Clínicas a las que pertenece este usuario, con su rol en cada una.
     */
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
            ->withPivot(['role_id', 'status', 'joined_at']);
    }

    /**
     * Requerido por el contrato HasTenants de Filament: las clínicas que
     * este usuario puede ver/seleccionar en el panel "app". Solo las
     * membresías activas — un usuario invitado (status=invited) o
     * deshabilitado (status=disabled) no debe poder entrar a esa clínica
     * todavía.
     */
    public function getTenants(\Filament\Panel $panel): Collection
    {
        return $this->tenants()->wherePivot('status', 'active')->get();
    }

    /**
     * Requerido por el contrato HasTenants: valida que el usuario
     * realmente pertenezca (activo) a la clínica antes de dejarlo entrar,
     * incluso si llega con una URL /app/{tenant}/... escrita a mano.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenants()
            ->wherePivot('status', 'active')
            ->whereKey($tenant->getKey())
            ->exists();
    }
}
