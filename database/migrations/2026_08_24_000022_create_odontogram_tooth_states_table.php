<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontogram_tooth_states', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('odontogram_id')->constrained()->cascadeOnDelete();
                    $table->string('tooth_fdi_code', 3);
                    $table->foreign('tooth_fdi_code')->references('fdi_code')->on('tooth_definitions');
                    $table->string('surface_code', 2)->nullable();
                    $table->foreign('surface_code')->references('code')->on('tooth_surfaces_catalog');
                    $table->foreignId('condition_type_id')->constrained('condition_types_catalog');
                    $table->string('notes')->nullable();
                    $table->string('color_override', 9)->nullable();
                    $table->index('tooth_fdi_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_tooth_states');
    }
};
