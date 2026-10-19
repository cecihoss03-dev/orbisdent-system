<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_emergency_contacts', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->string('name', 120);
                    $table->string('relationship', 60)->nullable();
                    $table->string('phone', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_emergency_contacts');
    }
};
