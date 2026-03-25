<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── ADMIN ──────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@clinica.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'active'   => true,
        ]);

        // ── MÉDICOS ────────────────────────────────────────────────────────
        $doctorsData = [
            [
                'name'           => 'Dr. Carlos Mendoza',
                'email'          => 'carlos.mendoza@clinica.com',
                'license_number' => 'CMH-001',
                'bio'            => 'Médico general con 15 años de experiencia.',
            ],
            [
                'name'           => 'Dra. Laura Pineda',
                'email'          => 'laura.pineda@clinica.com',
                'license_number' => 'CMH-002',
                'bio'            => 'Especialista en Pediatría y Medicina General.',
            ],
            [
                'name'           => 'Dr. Roberto Flores',
                'email'          => 'roberto.flores@clinica.com',
                'license_number' => 'CMH-003',
                'bio'            => 'Cardiólogo con enfoque preventivo.',
            ],
            [
                'name'           => 'Dra. Ana Suárez',
                'email'          => 'ana.suarez@clinica.com',
                'license_number' => 'CMH-004',
                'bio'            => 'Ginecóloga y especialista en salud femenina.',
            ],
            [
                'name'           => 'Dr. Miguel Torres',
                'email'          => 'miguel.torres@clinica.com',
                'license_number' => 'CMH-005',
                'bio'            => 'Traumatólogo y ortopedista.',
            ],
        ];

        foreach ($doctorsData as $data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
                'role'     => 'doctor',
                'active'   => true,
            ]);

            Doctor::create([
                'user_id'        => $user->id,
                'license_number' => $data['license_number'],
                'bio'            => $data['bio'],
            ]);
        }

        // ── PACIENTES ──────────────────────────────────────────────────────
        $patientsData = [
            ['name' => 'Juan García',      'email' => 'juan@mail.com',      'phone' => '+50498765432', 'birth' => '1990-03-15', 'gender' => 'M'],
            ['name' => 'María López',      'email' => 'maria@mail.com',     'phone' => '+50498765433', 'birth' => '1985-07-22', 'gender' => 'F'],
            ['name' => 'Pedro Hernández',  'email' => 'pedro@mail.com',     'phone' => '+50498765434', 'birth' => '1978-11-08', 'gender' => 'M'],
            ['name' => 'Sofía Ramírez',    'email' => 'sofia@mail.com',     'phone' => '+50498765435', 'birth' => '1995-01-30', 'gender' => 'F'],
            ['name' => 'Luis Martínez',    'email' => 'luis@mail.com',      'phone' => '+50498765436', 'birth' => '2000-06-12', 'gender' => 'M'],
            ['name' => 'Carmen Díaz',      'email' => 'carmen@mail.com',    'phone' => '+50498765437', 'birth' => '1970-09-25', 'gender' => 'F'],
            ['name' => 'Andrés Romero',    'email' => 'andres@mail.com',    'phone' => '+50498765438', 'birth' => '1988-04-18', 'gender' => 'M'],
            ['name' => 'Valentina Cruz',   'email' => 'valentina@mail.com', 'phone' => '+50498765439', 'birth' => '2003-12-05', 'gender' => 'F'],
            ['name' => 'Diego Morales',    'email' => 'diego@mail.com',     'phone' => '+50498765440', 'birth' => '1993-08-14', 'gender' => 'M'],
            ['name' => 'Isabella Vargas',  'email' => 'isabella@mail.com',  'phone' => '+50498765441', 'birth' => '1982-02-28', 'gender' => 'F'],
        ];

        foreach ($patientsData as $data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
                'role'     => 'patient',
                'active'   => true,
            ]);

            Patient::create([
                'user_id'    => $user->id,
                'phone'      => $data['phone'],
                'birth_date' => $data['birth'],
                'gender'     => $data['gender'],
            ]);
        }
    }
}
