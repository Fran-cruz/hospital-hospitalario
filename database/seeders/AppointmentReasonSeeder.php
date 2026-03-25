<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentReason;
use App\Models\Speciality;

class AppointmentReasonSeeder extends Seeder
{
    public function run(): void
    {
        // helper
        $sp = fn(string $name) => Speciality::where('name', $name)->first()->id;

        $reasons = [
            // Medicina General
            ['name' => 'Consulta general',                  'speciality' => 'Medicina General'],
            ['name' => 'Dolor de cabeza / migraña',         'speciality' => 'Medicina General'],
            ['name' => 'Fiebre y malestar general',         'speciality' => 'Medicina General'],
            ['name' => 'Dolor de garganta',                 'speciality' => 'Medicina General'],
            ['name' => 'Control de presión arterial',       'speciality' => 'Medicina General'],
            ['name' => 'Resfriado / gripe',                 'speciality' => 'Medicina General'],

            // Pediatría
            ['name' => 'Control de crecimiento infantil',   'speciality' => 'Pediatría'],
            ['name' => 'Vacunación',                        'speciality' => 'Pediatría'],
            ['name' => 'Fiebre en niños',                   'speciality' => 'Pediatría'],
            ['name' => 'Revisión de desarrollo',            'speciality' => 'Pediatría'],

            // Ginecología
            ['name' => 'Control prenatal',                  'speciality' => 'Ginecología'],
            ['name' => 'Papanicolau (PAP)',                 'speciality' => 'Ginecología'],
            ['name' => 'Irregularidad menstrual',           'speciality' => 'Ginecología'],
            ['name' => 'Planificación familiar',            'speciality' => 'Ginecología'],

            // Cardiología
            ['name' => 'Dolor en el pecho',                 'speciality' => 'Cardiología'],
            ['name' => 'Palpitaciones cardíacas',           'speciality' => 'Cardiología'],
            ['name' => 'Electrocardiograma (ECG)',          'speciality' => 'Cardiología'],
            ['name' => 'Control de colesterol',             'speciality' => 'Cardiología'],

            // Dermatología
            ['name' => 'Acné severo',                       'speciality' => 'Dermatología'],
            ['name' => 'Erupción cutánea / alergia piel',   'speciality' => 'Dermatología'],
            ['name' => 'Revisión de lunar / lunar atípico', 'speciality' => 'Dermatología'],

            // Traumatología
            ['name' => 'Dolor articular',                   'speciality' => 'Traumatología'],
            ['name' => 'Fractura / esguince',               'speciality' => 'Traumatología'],
            ['name' => 'Dolor de espalda crónico',          'speciality' => 'Traumatología'],
            ['name' => 'Lesión deportiva',                  'speciality' => 'Traumatología'],

            // Odontología
            ['name' => 'Limpieza dental',                   'speciality' => 'Odontología'],
            ['name' => 'Dolor de muela',                    'speciality' => 'Odontología'],
            ['name' => 'Extracción dental',                 'speciality' => 'Odontología'],

            // Oftalmología
            ['name' => 'Revisión de la vista',              'speciality' => 'Oftalmología'],
            ['name' => 'Ojo rojo / irritación ocular',      'speciality' => 'Oftalmología'],
            ['name' => 'Control de glaucoma',               'speciality' => 'Oftalmología'],
        ];

        foreach ($reasons as $r) {
            AppointmentReason::create([
                'name'         => $r['name'],
                'speciality_id' => $sp($r['speciality']),
            ]);
        }
    }
}
