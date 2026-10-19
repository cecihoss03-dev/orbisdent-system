<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                    $table->string('first_name', 80);
                    $table->string('last_name', 80);
                    $table->string('document_type', 20)->nullable();
                    $table->string('document_number', 30)->nullable();
                    $table->date('birth_date')->nullable();
                    $table->string('gender', 20)->nullable();
                    $table->string('address')->nullable();
                    $table->string('phone', 30)->nullable();
                    $table->string('email', 150)->nullable();
                    $table->string('occupation', 100)->nullable();
                    $table->string('marital_status', 30)->nullable();
                    $table->string('referred_by', 120)->nullable();
                    $table->string('profile_photo_url')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestampsTz();
                    $table->softDeletesTz();
                    $table->index('tenant_id');
        });

        // Documento único por clínica, pero solo cuando está presente (permite
        // varios pacientes sin documento registrado).
        DB::statement('CREATE UNIQUE INDEX idx_patients_tenant_doc ON patients (tenant_id, document_number) WHERE document_number IS NOT NULL');

        // Búsqueda de pacientes por nombre tolerante a errores tipográficos.
        DB::statement("CREATE INDEX idx_patients_name_trgm ON patients USING gin ((first_name || ' ' || last_name) gin_trgm_ops)");
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
