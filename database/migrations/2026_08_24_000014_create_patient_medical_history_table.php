<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medical_history', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('patient_id')->unique()->constrained()->cascadeOnDelete();
                    $table->string('blood_type', 5)->nullable();
                    $table->jsonb('allergies')->default('[]');
                    $table->jsonb('systemic_diseases')->default('[]');
                    $table->jsonb('current_medications')->default('[]');
                    $table->string('pregnancy_status', 20)->nullable();
                    $table->boolean('is_smoker')->default(false);
                    $table->string('alcohol_consumption', 30)->nullable();
                    $table->text('other_notes')->nullable();
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampTz('updated_at')->useCurrent();
        });

        DB::statement('CREATE INDEX idx_pmh_allergies ON patient_medical_history USING gin (allergies)');
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medical_history');
    }
};
