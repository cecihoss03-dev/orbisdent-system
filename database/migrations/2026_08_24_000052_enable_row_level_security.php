<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se aplica exactamente a las tablas cuyo modelo Eloquent usa el trait
        // BelongsToTenant — es la segunda capa de defensa del aislamiento
        // multi-tenant, independiente del Global Scope de la aplicación.
        $tenantScopedTables = [
            'patients',
            'patient_documents',
            'odontograms',
            'odontogram_attachments',
            'tooth_treatment_events',
            'periodontal_charts',
            'clinical_evolution_notes',
            'prescriptions',
            'chairs',
            'dentist_schedules',
            'schedule_exceptions',
            'appointments',
            'treatment_catalog',
            'treatment_plans',
            'invoices',
            'payments',
            'dentist_commissions',
            'suppliers',
            'supply_categories',
            'supplies',
            'supply_movements',
            'consent_forms',
            'tenant_users',
        ];

        foreach ($tenantScopedTables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("CREATE POLICY tenant_isolation_{$table} ON {$table} USING (tenant_id = current_setting('app.current_tenant_id')::BIGINT)");
        }
    }

    public function down(): void
    {
        $tenantScopedTables = [
            'patients',
            'patient_documents',
            'odontograms',
            'odontogram_attachments',
            'tooth_treatment_events',
            'periodontal_charts',
            'clinical_evolution_notes',
            'prescriptions',
            'chairs',
            'dentist_schedules',
            'schedule_exceptions',
            'appointments',
            'treatment_catalog',
            'treatment_plans',
            'invoices',
            'payments',
            'dentist_commissions',
            'suppliers',
            'supply_categories',
            'supplies',
            'supply_movements',
            'consent_forms',
            'tenant_users',
        ];

        foreach ($tenantScopedTables as $table) {
            DB::statement("DROP POLICY IF EXISTS tenant_isolation_{$table} ON {$table}");
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
