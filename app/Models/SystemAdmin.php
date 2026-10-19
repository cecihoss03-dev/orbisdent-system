<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Administrador general de la plataforma (guard "system_admin").
 * Solo puede entrar al panel "admin".
 */
class SystemAdmin extends Authenticatable implements FilamentUser
{
    use Notifiable;

    const UPDATED_AT = null; // la tabla solo registra created_at

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

    /**
     * Usa la tabla propia `system_admin_notifications` (la tabla `notifications`
     * ya pertenece a Orbis). readNotifications()/unreadNotifications() y el
     * envío de Filament se apoyan en esta relación.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(SystemAdminNotification::class, 'notifiable')->latest();
    }
}
