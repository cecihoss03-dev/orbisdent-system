<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->string('type', 60);
                    $table->jsonb('payload')->default('{}');
                    $table->timestampTz('read_at')->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index('user_id');
        });

        DB::statement('CREATE INDEX idx_notif_user_unread ON notifications (user_id) WHERE read_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
