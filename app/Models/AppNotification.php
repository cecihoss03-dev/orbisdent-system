<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mapea la tabla `notifications`. Se llama AppNotification (no
 * "Notification" a secas) para no chocar con
 * Illuminate\Notifications\Notification, que Laravel ya usa para su
 * propio sistema de notificaciones.
 */
class AppNotification extends Model
{
    protected $table = 'notifications';

    const UPDATED_AT = null; // la tabla solo registra created_at

    // Nota: sin trait BelongsToTenant — tenant_id es nullable y estas
    // notificaciones se consultan siempre por user_id, no por tenant.

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'payload',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
