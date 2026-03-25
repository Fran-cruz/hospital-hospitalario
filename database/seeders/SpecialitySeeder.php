<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Speciality;

class SpecialitySeeder extends Seeder
{
    public function run(): void
    {
        $specialities = [
            'Medicina General',
            'Pediatría',
            'Ginecología',
            'Cardiología',
            'Dermatología',
            'Traumatología',
            'Odontología',
            'Oftalmología',
        ];

        foreach ($specialities as $name) {
            Speciality::create(['name' => $name]);
        }
    }
}
