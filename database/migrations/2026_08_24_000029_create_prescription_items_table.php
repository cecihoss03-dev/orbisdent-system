<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
                    $table->string('medication_name', 150);
                    $table->string('dosage', 80)->nullable();
                    $table->string('frequency', 80)->nullable();
                    $table->string('duration', 80)->nullable();
                    $table->text('instructions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
