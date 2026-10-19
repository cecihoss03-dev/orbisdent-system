<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained();
                    $table->foreignId('treatment_plan_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('invoice_number', 40);
                    $table->date('issue_date');
                    $table->decimal('subtotal', 12, 2);
                    $table->decimal('tax', 12, 2)->default(0);
                    $table->decimal('total', 12, 2);
                    $table->string('status', 20)->default('issued');
                    $table->timestampTz('created_at')->useCurrent();
                    $table->unique(['tenant_id', 'invoice_number']);
                    $table->index('patient_id');
        });

        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_invoices_status CHECK (status IN ('issued','paid','partially_paid','void'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
