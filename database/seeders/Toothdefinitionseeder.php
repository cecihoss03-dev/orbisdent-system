<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ToothDefinitionSeeder extends Seeder
{
    /**
     * Puebla el catálogo global tooth_definitions con las 32 piezas de la
     * dentición permanente (cuadrantes 1-4, FDI 11-48) y las 20 piezas de
     * la dentición temporal/infantil (cuadrantes 5-8, FDI 51-85).
     */
    public function run(): void
    {
        // Nombre según posición dentro del cuadrante — dentición permanente (1-8)
        $adultPositionNames = [
            1 => 'Incisivo Central',
            2 => 'Incisivo Lateral',
            3 => 'Canino',
            4 => 'Primer Premolar',
            5 => 'Segundo Premolar',
            6 => 'Primer Molar',
            7 => 'Segundo Molar',
            8 => 'Tercer Molar (Cordal)',
        ];

        // Nombre según posición dentro del cuadrante — dentición temporal (1-5)
        $pediatricPositionNames = [
            1 => 'Incisivo Central',
            2 => 'Incisivo Lateral',
            3 => 'Canino',
            4 => 'Primer Molar',
            5 => 'Segundo Molar',
        ];

        // Arcada (Superior/Inferior) y lado (Derecho/Izquierdo) por cuadrante
        $quadrantDescriptors = [
            1 => ['arch' => 'Superior', 'side' => 'Derecho'],
            2 => ['arch' => 'Superior', 'side' => 'Izquierdo'],
            3 => ['arch' => 'Inferior', 'side' => 'Izquierdo'],
            4 => ['arch' => 'Inferior', 'side' => 'Derecho'],
            5 => ['arch' => 'Superior', 'side' => 'Derecho'],
            6 => ['arch' => 'Superior', 'side' => 'Izquierdo'],
            7 => ['arch' => 'Inferior', 'side' => 'Izquierdo'],
            8 => ['arch' => 'Inferior', 'side' => 'Derecho'],
        ];

        // Orden de visualización clínico estándar del odontograma
        // (arcada superior de derecha a izquierda, luego arcada inferior)
        $displayOrder = [
            // Adultos — arcada superior
            '18', '17', '16', '15', '14', '13', '12', '11',
            '21', '22', '23', '24', '25', '26', '27', '28',
            // Adultos — arcada inferior
            '48', '47', '46', '45', '44', '43', '42', '41',
            '31', '32', '33', '34', '35', '36', '37', '38',
            // Pediátrico — arcada superior
            '55', '54', '53', '52', '51', '61', '62', '63', '64', '65',
            // Pediátrico — arcada inferior
            '85', '84', '83', '82', '81', '71', '72', '73', '74', '75',
        ];

        $rows = [];

        // Dentición adulta: cuadrantes 1-4, posiciones 1-8
        foreach ([1, 2, 3, 4] as $quadrant) {
            foreach ($adultPositionNames as $position => $positionName) {
                $fdiCode = (string) (($quadrant * 10) + $position);
                $descriptor = $quadrantDescriptors[$quadrant];

                $rows[] = [
                    'fdi_code'       => $fdiCode,
                    'dentition_type' => 'adult',
                    'quadrant'       => $quadrant,
                    'name'           => "{$positionName} {$descriptor['arch']} {$descriptor['side']}",
                    'display_order'  => array_search($fdiCode, $displayOrder, true) + 1,
                ];
            }
        }

        // Dentición temporal: cuadrantes 5-8, posiciones 1-5
        foreach ([5, 6, 7, 8] as $quadrant) {
            foreach ($pediatricPositionNames as $position => $positionName) {
                $fdiCode = (string) (($quadrant * 10) + $position);
                $descriptor = $quadrantDescriptors[$quadrant];

                $rows[] = [
                    'fdi_code'       => $fdiCode,
                    'dentition_type' => 'pediatric',
                    'quadrant'       => $quadrant,
                    'name'           => "{$positionName} {$descriptor['arch']} {$descriptor['side']} (Temporal)",
                    'display_order'  => array_search($fdiCode, $displayOrder, true) + 1,
                ];
            }
        }

        DB::table('tooth_definitions')->insertOrIgnore($rows);

        $this->command?->info('tooth_definitions: '.count($rows).' piezas registradas (32 adultas + 20 temporales).');
    }
}