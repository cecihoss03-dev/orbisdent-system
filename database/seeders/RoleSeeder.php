<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Roles de sistema (tenant_id NULL), disponibles para todas las clínicas.
     * Requiere PermissionSeeder. Idempotente: deja cada rol EXACTAMENTE con
     * los permisos de abajo (sync), así que corrige permisos antiguos.
     *
     * Visibilidad:
     *  - patients.view_all     -> ve todos los pacientes (si no, solo los que tienen citas con él)
     *  - appointments.view_all -> ve toda la agenda (si no, solo sus citas)
     */
    public function run(): void
    {
        $all = Permission::pluck('id', 'name');

        $definitions = [
            // Dueño/gestor: control total (el sistema además le da acceso a todo permiso futuro).
            'Administrador de clínica' => $all->keys()->all(),

            // Odontólogo de planta: clínica completa, pero en agenda solo ve SUS citas.
            'Odontólogo' => [
                'patients.view', 'patients.view_all', 'patients.create', 'patients.update',
                'odontogram.view', 'odontogram.create', 'odontogram.update', 'odontogram.delete',
                'appointments.view', 'appointments.update',
                'treatments.view', 'treatments.create', 'treatments.update',
                'inventory.view',
            ],

            // Secretaria: agenda de todos, pacientes, cobros y recibos. Sin datos clínicos.
            'Recepcionista' => [
                'patients.view', 'patients.view_all', 'patients.create', 'patients.update',
                'appointments.view', 'appointments.view_all', 'appointments.create', 'appointments.update', 'appointments.delete',
                'treatments.view',
                'invoices.view', 'invoices.create', 'invoices.update',
            ],

            // Especialista externo: SOLO los pacientes y citas asignados a él.
            'Especialista externo' => [
                'patients.view', 'patients.update',
                'odontogram.view', 'odontogram.create', 'odontogram.update',
                'appointments.view', 'appointments.update',
                'treatments.view', 'treatments.create',
            ],
        ];

        foreach ($definitions as $name => $permissionNames) {
            $role = Role::firstOrCreate(
                ['tenant_id' => null, 'name' => $name],
                ['is_system' => true],
            );

            $role->permissions()->sync($all->only($permissionNames)->values()->all());
        }

        $this->command?->info('roles de sistema: '.count($definitions).' listos.');
    }
}
