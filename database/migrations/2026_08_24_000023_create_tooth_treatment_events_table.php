<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tooth_treatment_events', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->string('tooth_fdi_code', 3);
                    $table->foreign('tooth_fdi_code')->references('fdi_code')->on('tooth_definitions');
                    $table->string('surface_code', 2)->nullable();
                    $table->foreign('surface_code')->references('code')->on('tooth_surfaces_catalog');
                    $table->foreignId('condition_type_id')->constrained('condition_types_catalog');
                    $table->foreignId('dentist_id')->constrained('users');
                    $table->unsignedBigInteger('appointment_id')->nullable(); // FK diferida
                    $table->foreignId('odontogram_before_id')->nullable()->constrained('odontograms')->nullOnDelete();
                    $table->foreignId('odontogram_after_id')->nullable()->constrained('odontograms')->nullOnDelete();
                    $table->timestampTz('performed_at')->useCurrent();
                    $table->text('notes')->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index(['patient_id', 'performed_at']);
                    $table->index(['patient_id', 'tooth_fdi_code']);
                    $table->index(['dentist_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth_treatment_events');
    }
};
