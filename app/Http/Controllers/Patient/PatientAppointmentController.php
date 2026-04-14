<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReason;
use App\Models\Patient;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PatientAppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    public function index()
    {
        $appointments = auth()->user()->patient
            ->appointments()
            ->with(['doctor.user', 'reason.speciality'])
            ->latest('start_time')
            ->get()
            ->map(fn($appointment) => [
                'id' => $appointment->id,
                'status' => $appointment->status,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'doctor' => $appointment->doctor->user->name,
                'speciality' => $appointment->reason->speciality->name,
                'reason' => $appointment->reason->name,
                'notes' => $appointment->notes,
            ]);

        return inertia('Patient/Appointments/Index', compact('appointments'));
    }

    public function create()
    {
        $reasons = AppointmentReason::with('speciality')
            ->orderBy('name')
            ->get()
            ->map(fn($reason) => [
                'id' => $reason->id,
                'name' => $reason->name,
                'speciality_id' => $reason->speciality_id,
                'speciality' => $reason->speciality->name,
            ]);

        return inertia('Patient/Appointments/Create', compact('reasons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason_id' => 'required|exists:appointment_reasons,id',
            'date' => 'required|date|after_or_equal:today',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i',
            'doctor_id' => 'required|exists:doctors,id',
        ], [
            'date.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'reason_id.required' => 'Debes seleccionar un motivo de consulta.',
            'reason_id.exists' => 'El motivo seleccionado no es válido.',
            'doctor_id.required' => 'Debes seleccionar un médico disponible.',
            'doctor_id.exists' => 'El médico seleccionado no es válido.',
        ]);

        $patient = auth()->user()->patient
            ?? Patient::firstOrCreate(['user_id' => auth()->id()]);

        try {
            $appointment = $this->service->createPendingAppointment(
                $patient,
                $validated['reason_id'],
                $validated['date'],
                $validated['time_from'],
                $validated['time_to'],
                (int) $validated['doctor_id']
            );

            return redirect()
                ->route('patient.appointments.show', $appointment->id)
                ->with('success', 'Cita agendada exitosamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['availability' => $e->getMessage()]);
        } catch (\Throwable) {
            return back()->withErrors([
                'availability' => 'No hay disponibilidad en el rango seleccionado. Intenta otro rango.',
            ]);
        }
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['doctor.user', 'reason.speciality', 'patient.user']);

        return inertia('Patient/Appointments/Show', [
            'appointment' => [
                'id' => $appointment->id,
                'status' => $appointment->status,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'notes' => $appointment->notes,
                'doctor' => $appointment->doctor->user->name,
                'speciality' => $appointment->reason->speciality->name,
                'reason' => $appointment->reason->name,
                'reason_id' => $appointment->appointment_reason_id,
            ],
        ]);
    }

    public function confirm(Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        $appointment->update(['status' => 'confirmed']);

        return back()->with('success', 'Cita confirmada correctamente.');
    }

    public function cancel(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Cita cancelada. El horario quedó disponible.');
    }

    public function reprogram(Request $request, Appointment $appointment)
    {
        $this->authorize('reprogram', $appointment);

        $validated = $request->validate([
            'reason_id' => 'required|exists:appointment_reasons,id',
            'date' => 'required|date|after_or_equal:today',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i',
            'doctor_id' => 'required|exists:doctors,id',
        ], [
            'date.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'doctor_id.required' => 'Debes seleccionar un médico disponible.',
            'doctor_id.exists' => 'El médico seleccionado no es válido.',
        ]);

        try {
            $updated = $this->service->reprogramAppointment(
                $appointment,
                $validated['reason_id'],
                $validated['date'],
                $validated['time_from'],
                $validated['time_to'],
                (int) $validated['doctor_id']
            );

            return redirect()
                ->route('patient.appointments.show', $updated->id)
                ->with('success', 'Cita reprogramada exitosamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['availability' => $e->getMessage()]);
        } catch (\Throwable) {
            return back()->withErrors([
                'availability' => 'No se encontró disponibilidad para reprogramar en ese rango.',
            ]);
        }
    }
}
