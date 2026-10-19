<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chairs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->string('name', 60);
                    $table->string('status', 20)->default('available');
                    $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chairs');
    }
};
