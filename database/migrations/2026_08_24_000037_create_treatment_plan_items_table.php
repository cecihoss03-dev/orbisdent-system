<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plan_items', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('treatment_plan_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('treatment_id')->constrained('treatment_catalog');
                    $table->string('tooth_fdi_code', 3)->nullable();
                    $table->foreign('tooth_fdi_code')->references('fdi_code')->on('tooth_definitions');
                    $table->string('surface_code', 2)->nullable();
                    $table->foreign('surface_code')->references('code')->on('tooth_surfaces_catalog');
                    $table->integer('quantity')->default(1);
                    $table->decimal('unit_price', 10, 2);
                    $table->decimal('discount', 10, 2)->default(0);
                    $table->decimal('subtotal', 10, 2);
                    $table->string('status', 20)->default('pending');
                    $table->index('treatment_plan_id');
        });

        DB::statement("ALTER TABLE treatment_plan_items ADD CONSTRAINT chk_tpi_status CHECK (status IN ('pending','in_progress','completed','cancelled'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_items');
    }
};
