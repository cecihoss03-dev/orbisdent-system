<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condition_types_catalog', function (Blueprint $table) {
                    $table->id();
                    $table->string('code', 30)->unique();
                    $table->string('name', 80);
                    $table->string('category', 40);
                    $table->string('applies_to', 20)->default('surface');
                    $table->string('default_color', 9)->nullable();
                    $table->string('icon', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_types_catalog');
    }
};
