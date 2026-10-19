<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class SystemAdminNotification extends DatabaseNotification
{
    protected $table = 'system_admin_notifications';
}
