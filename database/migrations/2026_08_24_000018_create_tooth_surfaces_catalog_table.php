<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tooth_surfaces_catalog', function (Blueprint $table) {
                    $table->string('code', 2)->primary();
                    $table->string('name', 40);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth_surfaces_catalog');
    }
};
