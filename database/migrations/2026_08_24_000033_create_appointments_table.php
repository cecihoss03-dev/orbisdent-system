<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained('patients');
                    $table->foreignId('dentist_id')->constrained('users');
                    $table->foreignId('chair_id')->nullable()->constrained('chairs');
                    $table->unsignedBigInteger('treatment_id')->nullable(); // FK diferida (treatment_catalog)
                    $table->timestampTz('scheduled_start');
                    $table->timestampTz('scheduled_end');
                    $table->string('status', 20)->default('scheduled');
                    $table->string('reason')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampsTz();
                    $table->softDeletesTz();
                    $table->index(['tenant_id', 'scheduled_start']);
                    $table->index(['dentist_id', 'scheduled_start']);
                    $table->index('patient_id');
        });

        DB::statement("ALTER TABLE appointments ADD CONSTRAINT chk_appointments_status CHECK (status IN ('scheduled','confirmed','waiting','in_progress','completed','cancelled','no_show'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
