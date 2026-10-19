<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dentist_commissions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained();
                    $table->foreignId('treatment_plan_item_id')->constrained();
                    $table->string('commission_type', 20);
                    $table->decimal('commission_value', 10, 2);
                    $table->decimal('calculated_amount', 10, 2);
                    $table->string('status', 20)->default('pending');
                    $table->string('period', 10)->nullable();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index(['tenant_id', 'user_id', 'period']);
        });

        DB::statement("ALTER TABLE dentist_commissions ADD CONSTRAINT chk_dc_status CHECK (status IN ('pending','paid'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('dentist_commissions');
    }
};
