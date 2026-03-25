<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;

class DoctorAppointmentController extends Controller
{
    public function index()
    {
        $doctor = auth()->user()->doctor;

        $appointments = $doctor->appointments()
            ->with(['patient.user', 'reason.speciality'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->patient->user->name,
                'start'        => $a->start_time,
                'end'          => $a->end_time,
                'status'       => $a->status,
                'reason'       => $a->reason->name,
                'speciality'    => $a->reason->speciality->name,
                'patient_name' => $a->patient->user->name,
                'notes'        => $a->notes,
            ]);

        return inertia('Doctor/Appointments/Index', [
            'appointments' => $appointments,
            'whatsapp_number' => config('clinic.admin_whatsapp', '+50498765432'),
        ]);
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient.user', 'reason.speciality', 'doctor.user']);

        return inertia('Doctor/Appointments/Show', [
            'appointment'     => $appointment,
            'whatsapp_number' => config('clinic.admin_whatsapp', '+50498765432'),
        ]);
    }
}
