<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;

class DoctorAppointmentController extends Controller
{
    public function index()
    {
        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            abort(403, 'No tienes un perfil de médico asociado.');
        }

        $appointments = $doctor->appointments()
            ->with(['patient.user', 'reason.speciality'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                // Campos que FullCalendar necesita
                'start' => \Carbon\Carbon::parse($a->start_time)->toIso8601String(), // parsing to ISO to avoid FullCalendar errors
                'end' => \Carbon\Carbon::parse($a->end_time)->toIso8601String(),
                // Campos para el modal
                'status'       => $a->status,
                'patient_name' => $a->patient->user->name,
                'reason'       => $a->reason->name,
                'speciality'    => $a->reason->speciality->name,
                'notes'        => $a->notes,
            ]);

        return inertia('Doctor/Appointments/Index', [
            'appointments'    => $appointments,
            'whatsapp_number' => config('clinic.admin_whatsapp', '50494599288'),
        ]);
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient.user', 'reason.speciality', 'doctor.user']);

        return inertia('Doctor/Appointments/Show', [
            'appointment'     => $appointment,
            'whatsapp_number' => config('clinic.admin_whatsapp', '50494599288'),
        ]);
    }
}
