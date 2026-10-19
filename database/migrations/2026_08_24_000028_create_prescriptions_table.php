<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('dentist_id')->constrained('users');
                    $table->timestampTz('issued_at')->useCurrent();
                    $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
