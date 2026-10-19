<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
                    $table->string('business_name', 150);
                    $table->string('tax_id', 30)->nullable();
                    $table->string('address')->nullable();
                    $table->string('phone', 30)->nullable();
                    $table->string('logo_url')->nullable();
                    $table->string('status', 20)->default('trial');
                    $table->string('timezone', 60)->default('America/La_Paz');
                    $table->string('currency', 10)->default('BOB');
                    $table->jsonb('cancellation_policy')->default('{}');
                    $table->jsonb('settings')->default('{}');
                    $table->timestampsTz();
                    $table->softDeletesTz();
                    $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
