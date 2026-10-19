<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained();
                    $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
                    $table->foreignId('treatment_plan_id')->nullable()->constrained()->nullOnDelete();
                    $table->decimal('amount', 10, 2);
                    $table->string('payment_method', 30);
                    $table->date('payment_date');
                    $table->string('reference_number', 60)->nullable();
                    $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('status', 20)->default('completed');
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index('patient_id');
                    $table->index(['tenant_id', 'payment_date']);
        });

        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_status CHECK (status IN ('completed','refunded','voided'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
