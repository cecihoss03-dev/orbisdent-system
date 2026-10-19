<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_documents', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
                    $table->string('type', 40);
                    $table->string('file_url');
                    $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestampTz('uploaded_at')->useCurrent();
                    $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
    }
};
