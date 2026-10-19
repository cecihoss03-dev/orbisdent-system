<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabla propia para las notificaciones del panel admin. La tabla `notifications`
// ya la usa Orbis para otra cosa (user_id/payload), por eso no se reutiliza.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_admin_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->jsonb('data'); // jsonb: Filament filtra con data->>'format' en PostgreSQL
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_admin_notifications');
    }
};
