<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('action', 20);
                    $table->string('entity_type', 80);
                    $table->unsignedBigInteger('entity_id');
                    $table->jsonb('old_values')->nullable();
                    $table->jsonb('new_values')->nullable();
                    $table->ipAddress('ip_address')->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index(['tenant_id', 'entity_type', 'entity_id']);
                    $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
