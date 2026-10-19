<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodontal_measurements', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('periodontal_chart_id')->constrained()->cascadeOnDelete();
                    $table->string('tooth_fdi_code', 3);
                    $table->foreign('tooth_fdi_code')->references('fdi_code')->on('tooth_definitions');
                    $table->string('site', 20);
                    $table->smallInteger('probing_depth_mm')->nullable();
                    $table->smallInteger('recession_mm')->nullable();
                    $table->boolean('bleeding_on_probing')->default(false);
                    $table->boolean('plaque_present')->default(false);
                    $table->smallInteger('mobility_grade')->nullable();
                    $table->smallInteger('furcation_grade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodontal_measurements');
    }
};
