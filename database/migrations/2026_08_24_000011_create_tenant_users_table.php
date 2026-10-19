<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_users', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('role_id')->constrained('roles');
                    $table->string('status', 20)->default('active');
                    $table->timestampTz('joined_at')->useCurrent();
                    $table->unique(['tenant_id', 'user_id']);
                    $table->index('tenant_id');
                    $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};
