<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontogram_attachments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('odontogram_id')->nullable()->constrained()->cascadeOnDelete();
                    $table->foreignId('tooth_treatment_event_id')->nullable()->constrained()->cascadeOnDelete();
                    $table->string('type', 20);
                    $table->string('file_url');
                    $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampTz('uploaded_at')->useCurrent();
                    $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_attachments');
    }
};
