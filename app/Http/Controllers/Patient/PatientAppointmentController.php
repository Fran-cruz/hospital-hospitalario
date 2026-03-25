<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReason;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class PatientAppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    // ── Historial ──────────────────────────────────────────────────────────
    public function index()
    {
        $appointments = auth()->user()->patient
            ->appointments()
            ->with(['doctor.user', 'reason.speciality'])
            ->latest('start_time')
            ->get()
            ->map(fn($a) => [
                'id'         => $a->id,
                'status'     => $a->status,
                'start_time' => $a->start_time,
                'end_time'   => $a->end_time,
                'doctor'     => $a->doctor->user->name,
                'speciality'  => $a->reason->speciality->name,
                'reason'     => $a->reason->name,
                'notes'      => $a->notes,
            ]);

        return inertia('Patient/Appointments/Index', compact('appointments'));
    }

    // ── Formulario nueva cita ──────────────────────────────────────────────
    public function create()
    {
        $reasons = AppointmentReason::with('speciality')
            ->orderBy('name')
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'name'     => $r->name,
                'speciality'=> $r->speciality->name,
            ]);

        return inertia('Patient/Appointments/Create', compact('reasons'));
    }

    // ── Crear cita (asignación automática) ────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'reason_id' => 'required|exists:appointment_reasons,id',
            'from'      => 'required|date|after_or_equal:today',
            'to'        => 'required|date|after:from',
        ]);

        try {
            $appointment = $this->service->createPendingAppointment(
                auth()->user()->patient,
                $request->reason_id,
                $request->from,
                $request->to
            );

            return redirect()
                ->route('patient.appointments.show', $appointment->id)
                ->with('success', '¡Cita solicitada! Quedó en estado Pendiente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'availability' => 'No hay disponibilidad en el rango seleccionado. Intenta ampliar las fechas o contacta al administrador.'
            ]);
        }
    }

    // ── Detalle ────────────────────────────────────────────────────────────
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['doctor.user', 'reason.speciality', 'patient.user']);

        return inertia('Patient/Appointments/Show', [
            'appointment' => [
                'id'         => $appointment->id,
                'status'     => $appointment->status,
                'start_time' => $appointment->start_time,
                'end_time'   => $appointment->end_time,
                'notes'      => $appointment->notes,
                'doctor'     => $appointment->doctor->user->name,
                'speciality'  => $appointment->reason->speciality->name,
                'reason'     => $appointment->reason->name,
            ]
        ]);
    }

    // ── Confirmar ──────────────────────────────────────────────────────────
    public function confirm(Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        $appointment->update(['status' => 'confirmed']);

        return back()->with('success', 'Cita confirmada correctamente.');
    }

    // ── Cancelar ──────────────────────────────────────────────────────────
    public function cancel(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Cita cancelada. El horario quedó disponible.');
    }

    // ── Reprogramar ────────────────────────────────────────────────────────
    public function reprogram(Request $request, Appointment $appointment)
    {
        $this->authorize('reprogram', $appointment);

        $request->validate([
            'reason_id' => 'required|exists:appointment_reasons,id',
            'from'      => 'required|date|after_or_equal:today',
            'to'        => 'required|date|after:from',
        ]);

        // Cancelar la cita actual
        $appointment->update(['status' => 'cancelled']);

        try {
            $new = $this->service->createPendingAppointment(
                auth()->user()->patient,
                $request->reason_id,
                $request->from,
                $request->to
            );

            return redirect()
                ->route('patient.appointments.show', $new->id)
                ->with('success', 'Cita reprogramada. Nueva cita en estado Pendiente.');

        } catch (\Exception $e) {
            // Restaurar si falló (opcional: mantenerla cancelada y avisar)
            return back()->withErrors([
                'availability' => 'No se encontró disponibilidad para reprogramar. Contacta al administrador.'
            ]);
        }
    }
}
