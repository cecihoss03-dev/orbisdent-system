<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_evolution_notes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->unsignedBigInteger('appointment_id')->nullable(); // FK diferida
                    $table->foreignId('dentist_id')->constrained('users');
                    $table->date('visit_date');
                    $table->string('reason')->nullable();
                    $table->string('diagnosis_cie10', 10)->nullable();
                    $table->foreign('diagnosis_cie10')->references('code')->on('cie10_dental_catalog');
                    $table->text('subjective')->nullable();
                    $table->text('objective')->nullable();
                    $table->text('assessment')->nullable();
                    $table->text('plan')->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index(['patient_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_evolution_notes');
    }
};
