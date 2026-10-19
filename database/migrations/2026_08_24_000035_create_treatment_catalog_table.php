<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_catalog', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->string('name', 150);
                    $table->text('description')->nullable();
                    $table->string('category', 60)->nullable();
                    $table->decimal('base_price', 10, 2)->default(0);
                    $table->integer('estimated_duration_minutes')->nullable();
                    $table->boolean('requires_tooth')->default(true);
                    $table->boolean('is_active')->default(true);
                    $table->timestampTz('created_at')->useCurrent();
                    $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_catalog');
    }
};
