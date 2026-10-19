<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ToothSurfaceCatalogSeeder extends Seeder
{
    /**
     * Puebla el catálogo global tooth_surfaces_catalog con las
     * superficies/caras dentales estándar usadas en el odontograma.
     */
    public function run(): void
    {
        $surfaces = [
            ['code' => 'O', 'name' => 'Oclusal'],
            ['code' => 'M', 'name' => 'Mesial'],
            ['code' => 'D', 'name' => 'Distal'],
            ['code' => 'V', 'name' => 'Vestibular'],
            ['code' => 'L', 'name' => 'Lingual'],
            ['code' => 'P', 'name' => 'Palatina'],
            ['code' => 'I', 'name' => 'Incisal'],
        ];

        DB::table('tooth_surfaces_catalog')->insertOrIgnore($surfaces);

        $this->command?->info('tooth_surfaces_catalog: '.count($surfaces).' superficies registradas.');
    }
}