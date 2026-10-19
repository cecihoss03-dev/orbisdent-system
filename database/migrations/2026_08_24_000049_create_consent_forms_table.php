<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_forms', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('treatment_plan_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('form_type', 60);
                    $table->string('signed_file_url')->nullable();
                    $table->timestampTz('signed_at')->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_forms');
    }
};
