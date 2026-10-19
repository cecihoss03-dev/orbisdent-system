<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionTypeCatalogSeeder extends Seeder
{
    /**
     * Puebla el catálogo global condition_types_catalog con los estados
     * diagnósticos y tratamientos más comunes que se pintan sobre el
     * odontograma (SVG/Canvas) en el frontend.
     */
    public function run(): void
    {
        $conditions = [
            [
                'code'          => 'caries',
                'name'          => 'Caries',
                'category'      => 'diagnostico',
                'applies_to'    => 'surface',
                'default_color' => '#EF4444',
                'icon'          => 'alert-triangle',
            ],
            [
                'code'          => 'obturado',
                'name'          => 'Obturado / Resina Realizada',
                'category'      => 'tratamiento',
                'applies_to'    => 'surface',
                'default_color' => '#3B82F6',
                'icon'          => 'square',
            ],
            [
                'code'          => 'ausente',
                'name'          => 'Diente Ausente / Extraído',
                'category'      => 'diagnostico',
                'applies_to'    => 'whole_tooth',
                'default_color' => '#111827',
                'icon'          => 'x',
            ],
            [
                'code'          => 'corona',
                'name'          => 'Corona',
                'category'      => 'protesis',
                'applies_to'    => 'whole_tooth',
                'default_color' => '#F59E0B',
                'icon'          => 'crown',
            ],
            [
                'code'          => 'endodoncia',
                'name'          => 'Endodoncia / Tratamiento de Conducto',
                'category'      => 'tratamiento',
                'applies_to'    => 'whole_tooth',
                'default_color' => '#8B5CF6',
                'icon'          => 'activity',
            ],
            [
                'code'          => 'protesis_removible',
                'name'          => 'Prótesis Removible / Implante',
                'category'      => 'protesis',
                'applies_to'    => 'whole_tooth',
                'default_color' => '#10B981',
                'icon'          => 'link',
            ],
            [
                'code'          => 'recesion_fractura',
                'name'          => 'Recesión Gingival / Fractura',
                'category'      => 'periodontal',
                'applies_to'    => 'both',
                'default_color' => '#EC4899',
                'icon'          => 'zap',
            ],
            [
                'code'          => 'sano',
                'name'          => 'Sano / Sin Hallazgos',
                'category'      => 'diagnostico',
                'applies_to'    => 'both',
                'default_color' => '#6B7280',
                'icon'          => 'check',
            ],
        ];

        DB::table('condition_types_catalog')->insertOrIgnore($conditions);

        $this->command?->info('condition_types_catalog: '.count($conditions).' condiciones registradas.');
    }
}