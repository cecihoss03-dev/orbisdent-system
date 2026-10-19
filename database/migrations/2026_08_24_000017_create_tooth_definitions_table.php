<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tooth_definitions', function (Blueprint $table) {
                    $table->string('fdi_code', 3)->primary();
                    $table->string('dentition_type', 10);
                    $table->smallInteger('quadrant');
                    $table->string('name', 60);
                    $table->smallInteger('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth_definitions');
    }
};
