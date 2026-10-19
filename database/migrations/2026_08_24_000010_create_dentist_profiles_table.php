<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dentist_profiles', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                    $table->string('license_number', 60)->nullable();
                    $table->string('specialty', 100)->nullable();
                    $table->string('signature_image_url')->nullable();
                    $table->text('bio')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dentist_profiles');
    }
};
