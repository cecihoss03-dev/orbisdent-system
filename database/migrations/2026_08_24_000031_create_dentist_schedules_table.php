<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dentist_schedules', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('chair_id')->nullable()->constrained('chairs')->nullOnDelete();
                    $table->smallInteger('day_of_week');
                    $table->time('start_time');
                    $table->time('end_time');
                    $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dentist_schedules');
    }
};
