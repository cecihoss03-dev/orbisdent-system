<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plans', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained();
                    $table->foreignId('created_by')->constrained('users');
                    $table->string('status', 20)->default('draft');
                    $table->decimal('total_amount', 12, 2)->default(0);
                    $table->decimal('discount_amount', 12, 2)->default(0);
                    $table->timestampTz('approved_at')->nullable();
                    $table->timestampsTz();
                    $table->index('patient_id');
                    $table->index(['tenant_id', 'status']);
        });

        DB::statement("ALTER TABLE treatment_plans ADD CONSTRAINT chk_treatment_plans_status CHECK (status IN ('draft','approved','rejected','in_progress','completed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plans');
    }
};
