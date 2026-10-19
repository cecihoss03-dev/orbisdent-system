<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontograms', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->unsignedBigInteger('appointment_id')->nullable(); // FK diferida, ver add_deferred_foreign_keys
                    $table->string('dentition_type', 10)->default('adult');
                    $table->boolean('is_current')->default(true);
                    $table->foreignId('recorded_by')->constrained('users');
                    $table->timestampTz('recorded_at')->useCurrent();
                    $table->text('general_notes')->nullable();
                    $table->index(['patient_id', 'recorded_at']);
        });

        // Solo puede existir un odontograma vigente por paciente.
        DB::statement('CREATE UNIQUE INDEX idx_odontograms_current ON odontograms (patient_id) WHERE is_current');
    }

    public function down(): void
    {
        Schema::dropIfExists('odontograms');
    }
};
