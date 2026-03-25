<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\AppointmentReason;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $statuses = ['pending', 'confirmed', 'cancelled'];

        // Mapeamos razones por especialidad para asignar médico correcto
        $reasons = AppointmentReason::with('speciality')->get();

        $appointments = [
            // Juan García → Cardiología
            [
                'patient_email' => 'juan@mail.com',
                'reason_name'   => 'Dolor en el pecho',
                'doctor_lic'    => 'CMH-003',
                'start'         => now()->subDays(10)->setTime(9, 0),
                'status'        => 'confirmed',
                'notes'         => 'Paciente refiere dolor leve al esfuerzo.',
            ],
            // Juan García → Medicina General
            [
                'patient_email' => 'juan@mail.com',
                'reason_name'   => 'Control de presión arterial',
                'doctor_lic'    => 'CMH-001',
                'start'         => now()->addDays(3)->setTime(10, 0),
                'status'        => 'pending',
                'notes'         => null,
            ],
            // María López → Ginecología
            [
                'patient_email' => 'maria@mail.com',
                'reason_name'   => 'Control prenatal',
                'doctor_lic'    => 'CMH-004',
                'start'         => now()->subDays(5)->setTime(11, 0),
                'status'        => 'confirmed',
                'notes'         => 'Semana 20 de gestación.',
            ],
            // María López → Dermatología
            [
                'patient_email' => 'maria@mail.com',
                'reason_name'   => 'Revisión de lunar / lunar atípico',
                'doctor_lic'    => 'CMH-002',
                'start'         => now()->addDays(7)->setTime(9, 30),
                'status'        => 'pending',
                'notes'         => null,
            ],
            // Pedro → Traumatología
            [
                'patient_email' => 'pedro@mail.com',
                'reason_name'   => 'Dolor articular',
                'doctor_lic'    => 'CMH-005',
                'start'         => now()->subDays(15)->setTime(14, 0),
                'status'        => 'cancelled',
                'notes'         => 'Cancelada por el paciente.',
            ],
            // Pedro → Medicina General
            [
                'patient_email' => 'pedro@mail.com',
                'reason_name'   => 'Consulta general',
                'doctor_lic'    => 'CMH-003',
                'start'         => now()->addDays(2)->setTime(8, 0),
                'status'        => 'pending',
                'notes'         => null,
            ],
            // Sofía → Ginecología
            [
                'patient_email' => 'sofia@mail.com',
                'reason_name'   => 'Papanicolau (PAP)',
                'doctor_lic'    => 'CMH-004',
                'start'         => now()->addDays(1)->setTime(10, 0),
                'status'        => 'confirmed',
                'notes'         => null,
            ],
            // Luis → Pediatría
            [
                'patient_email' => 'luis@mail.com',
                'reason_name'   => 'Vacunación',
                'doctor_lic'    => 'CMH-001',
                'start'         => now()->subDays(3)->setTime(9, 0),
                'status'        => 'confirmed',
                'notes'         => 'Vacuna influenza estacional.',
            ],
            // Carmen → Cardiología
            [
                'patient_email' => 'carmen@mail.com',
                'reason_name'   => 'Electrocardiograma (ECG)',
                'doctor_lic'    => 'CMH-003',
                'start'         => now()->addDays(5)->setTime(11, 30),
                'status'        => 'pending',
                'notes'         => null,
            ],
            // Andrés → Traumatología
            [
                'patient_email' => 'andres@mail.com',
                'reason_name'   => 'Lesión deportiva',
                'doctor_lic'    => 'CMH-005',
                'start'         => now()->addDays(4)->setTime(15, 0),
                'status'        => 'confirmed',
                'notes'         => 'Lesión en rodilla derecha.',
            ],
            // Valentina → Dermatología
            [
                'patient_email' => 'valentina@mail.com',
                'reason_name'   => 'Acné severo',
                'doctor_lic'    => 'CMH-002',
                'start'         => now()->subDays(8)->setTime(10, 30),
                'status'        => 'confirmed',
                'notes'         => null,
            ],
            // Diego → Odontología
            [
                'patient_email' => 'diego@mail.com',
                'reason_name'   => 'Limpieza dental',
                'doctor_lic'    => 'CMH-004',
                'start'         => now()->addDays(6)->setTime(8, 30),
                'status'        => 'pending',
                'notes'         => null,
            ],
            // Isabella → Oftalmología
            [
                'patient_email' => 'isabella@mail.com',
                'reason_name'   => 'Revisión de la vista',
                'doctor_lic'    => 'CMH-005',
                'start'         => now()->addDays(8)->setTime(9, 0),
                'status'        => 'pending',
                'notes'         => null,
            ],
            // Extra citas pasadas para el dashboard
            [
                'patient_email' => 'juan@mail.com',
                'reason_name'   => 'Resfriado / gripe',
                'doctor_lic'    => 'CMH-001',
                'start'         => now()->subDays(30)->setTime(8, 0),
                'status'        => 'confirmed',
                'notes'         => null,
            ],
            [
                'patient_email' => 'maria@mail.com',
                'reason_name'   => 'Fiebre y malestar general',
                'doctor_lic'    => 'CMH-001',
                'start'         => now()->subDays(20)->setTime(10, 0),
                'status'        => 'cancelled',
                'notes'         => 'Paciente no se presentó.',
            ],
            [
                'patient_email' => 'carmen@mail.com',
                'reason_name'   => 'Control de colesterol',
                'doctor_lic'    => 'CMH-003',
                'start'         => now()->subDays(12)->setTime(14, 30),
                'status'        => 'confirmed',
                'notes'         => null,
            ],
        ];

        foreach ($appointments as $appt) {
            $patient = Patient::whereHas('user', fn($q) =>
            $q->where('email', $appt['patient_email'])
            )->first();

            $doctor = \App\Models\Doctor::where('license_number', $appt['doctor_lic'])->first();

            $reason = AppointmentReason::where('name', $appt['reason_name'])->first();

            $start = Carbon::parse($appt['start']);
            $end   = $start->copy()->addMinutes(30);

            Appointment::create([
                'patient_id'             => $patient->id,
                'doctor_id'              => $doctor->id,
                'appointment_reason_id'  => $reason->id,
                'start_time'             => $start,
                'end_time'               => $end,
                'status'                 => $appt['status'],
                'notes'                  => $appt['notes'],
            ]);
        }
    }
}
