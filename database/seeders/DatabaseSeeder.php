<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Orquesta el seeding de la base de datos.
     *
     * Los catálogos van primero porque son tablas globales de las que
     * dependen los módulos clínicos (odontograma, periodontograma, etc.).
     */
    public function run(): void
    {
        $this->call([
            ToothSurfaceCatalogSeeder::class,
            ToothDefinitionSeeder::class,
            ConditionTypeCatalogSeeder::class,
        ]);
    }
}