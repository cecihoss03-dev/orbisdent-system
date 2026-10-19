<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->string('name', 150);
                    $table->string('contact', 120)->nullable();
                    $table->string('phone', 30)->nullable();
                    $table->string('email', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
