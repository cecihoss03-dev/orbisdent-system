<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medical_history_logs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->jsonb('snapshot');
                    $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampTz('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medical_history_logs');
    }
};
