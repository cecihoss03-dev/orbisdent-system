<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodontal_charts', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->unsignedBigInteger('appointment_id')->nullable(); // FK diferida
                    $table->foreignId('recorded_by')->constrained('users');
                    $table->timestampTz('recorded_at')->useCurrent();
                    $table->text('notes')->nullable();
                    $table->index(['patient_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodontal_charts');
    }
};
