<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_batches', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('supply_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                    $table->string('batch_number', 60)->nullable();
                    $table->date('expiration_date')->nullable();
                    $table->decimal('quantity_received', 10, 2);
                    $table->decimal('quantity_remaining', 10, 2);
                    $table->timestampTz('received_at')->useCurrent();
                    $table->index('supply_id');
        });

        DB::statement('CREATE INDEX idx_sb_expiration ON supply_batches (expiration_date) WHERE quantity_remaining > 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_batches');
    }
};
