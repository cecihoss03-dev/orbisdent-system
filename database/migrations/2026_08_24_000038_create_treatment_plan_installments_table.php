<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plan_installments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('treatment_plan_id')->constrained()->cascadeOnDelete();
                    $table->date('due_date');
                    $table->decimal('amount', 10, 2);
                    $table->string('status', 20)->default('pending');
                    $table->index('treatment_plan_id');
        });

        DB::statement("ALTER TABLE treatment_plan_installments ADD CONSTRAINT chk_tpins_status CHECK (status IN ('pending','paid','overdue'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_installments');
    }
};
