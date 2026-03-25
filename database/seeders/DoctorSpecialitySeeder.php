<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Speciality;

class DoctorSpecialitySeeder extends Seeder
{
    public function run(): void
    {
        // Doctor 1 (Carlos Mendoza) → Medicina General + Pediatría
        $doc1 = Doctor::where('license_number', 'CMH-001')->first();
        $doc1->specialities()->attach([
            Speciality::where('name', 'Medicina General')->first()->id,
            Speciality::where('name', 'Pediatría')->first()->id,
        ]);

        // Doctor 2 (Laura Pineda) → Pediatría + Dermatología
        $doc2 = Doctor::where('license_number', 'CMH-002')->first();
        $doc2->specialities()->attach([
            Speciality::where('name', 'Pediatría')->first()->id,
            Speciality::where('name', 'Dermatología')->first()->id,
        ]);

        // Doctor 3 (Roberto Flores) → Cardiología + Medicina General
        $doc3 = Doctor::where('license_number', 'CMH-003')->first();
        $doc3->specialities()->attach([
            Speciality::where('name', 'Cardiología')->first()->id,
            Speciality::where('name', 'Medicina General')->first()->id,
        ]);

        // Doctor 4 (Ana Suárez) → Ginecología + Odontología
        $doc4 = Doctor::where('license_number', 'CMH-004')->first();
        $doc4->specialities()->attach([
            Speciality::where('name', 'Ginecología')->first()->id,
            Speciality::where('name', 'Odontología')->first()->id,
        ]);

        // Doctor 5 (Miguel Torres) → Traumatología + Oftalmología
        $doc5 = Doctor::where('license_number', 'CMH-005')->first();
        $doc5->specialities()->attach([
            Speciality::where('name', 'Traumatología')->first()->id,
            Speciality::where('name', 'Oftalmología')->first()->id,
        ]);
    }
}
