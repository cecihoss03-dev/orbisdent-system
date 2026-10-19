<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_status_history', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
                    $table->string('old_status', 20)->nullable();
                    $table->string('new_status', 20);
                    $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampTz('changed_at')->useCurrent();
                    $table->string('reason')->nullable();
                    $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_status_history');
    }
};
