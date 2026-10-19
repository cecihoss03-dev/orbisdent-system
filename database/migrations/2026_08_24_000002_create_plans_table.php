<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
                    $table->id();
                    $table->string('name', 100);
                    $table->string('slug', 100)->unique();
                    $table->decimal('price', 10, 2)->default(0);
                    $table->string('billing_cycle', 20)->default('monthly');
                    $table->integer('max_users')->nullable();
                    $table->integer('max_patients')->nullable();
                    $table->jsonb('features')->default('{}');
                    $table->boolean('is_active')->default(true);
                    $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
