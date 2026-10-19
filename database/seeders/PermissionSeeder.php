<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Catálogo global de permisos "modulo.accion". Idempotente.
     */
    public function run(): void
    {
        $modules = [
            'patients'     => 'Pacientes',
            'odontogram'   => 'Odontograma e historia clínica',
            'appointments' => 'Agenda y citas',
            'treatments'   => 'Tratamientos y presupuestos',
            'invoices'     => 'Facturación y cobros',
            'reports'      => 'Reportes y comisiones',
            'inventory'    => 'Inventario',
            'staff'        => 'Personal y roles',
        ];

        foreach ($modules as $module => $label) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}"], ['module' => $label]);
            }
        }

        // Alcance de visibilidad: sin estos permisos el usuario solo ve LO ASIGNADO a él.
        Permission::firstOrCreate(['name' => 'patients.view_all'], ['module' => 'Pacientes']);
        Permission::firstOrCreate(['name' => 'appointments.view_all'], ['module' => 'Agenda y citas']);

        $this->command?->info('permissions: '.Permission::count().' registrados.');
    }
}
