<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_movements', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('supply_id')->constrained();
                    $table->foreignId('batch_id')->nullable()->constrained('supply_batches')->nullOnDelete();
                    $table->string('movement_type', 20);
                    $table->decimal('quantity', 10, 2);
                    $table->foreignId('related_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
                    $table->foreignId('related_treatment_plan_item_id')->nullable()->constrained('treatment_plan_items')->nullOnDelete();
                    $table->string('notes')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index(['tenant_id', 'created_at']);
                    $table->index('supply_id');
        });

        DB::statement("ALTER TABLE supply_movements ADD CONSTRAINT chk_sm_type CHECK (movement_type IN ('in','out','adjustment'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_movements');
    }
};
