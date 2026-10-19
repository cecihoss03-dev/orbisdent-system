<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('category_id')->nullable()->constrained('supply_categories')->nullOnDelete();
                    $table->string('name', 150);
                    $table->string('unit_measure', 20);
                    $table->decimal('min_stock', 10, 2)->default(0);
                    $table->decimal('current_stock', 10, 2)->default(0);
                    $table->decimal('unit_cost', 10, 2)->nullable();
                    $table->boolean('tracks_expiration')->default(false);
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index('tenant_id');
        });

        // Acelera Supply::lowStock() (insumos bajo el mínimo).
        DB::statement('CREATE INDEX idx_supplies_low_stock ON supplies (tenant_id) WHERE current_stock <= min_stock');
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
