<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    public function index(Request $request)
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user', 'reason.speciality'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->doctor_id, fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->from, fn($q) => $q->whereDate('start_time', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('start_time', '<=', $request->to))
            ->orderBy('start_time')
            ->get()
            ->map(function ($appointment) {
                $start = Carbon::parse($appointment->start_time);
                $end = Carbon::parse($appointment->end_time);

                return [
                    'id' => $appointment->id,
                    'start_time' => $start->format('d/m/Y H:i'),
                    'end_time' => $end->format('d/m/Y H:i'),
                    'start_raw' => $start->toDateTimeString(),
                    'end_raw' => $end->toDateTimeString(),
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                    'patient' => $appointment->patient->user->name,
                    'doctor' => $appointment->doctor->user->name,
                    'speciality' => $appointment->reason->speciality->name,
                    'reason' => $appointment->reason->name,
                    'appointment_reason_id' => $appointment->appointment_reason_id,
                ];
            });

        $doctors = Doctor::with('user')
            ->get()
            ->map(fn($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->user->name,
            ]);

        return inertia('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'filters' => $request->only(['status', 'doctor_id', 'from', 'to']),
        ]);
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'doctor.user', 'reason.speciality']);

        return response()->json([
            'id' => $appointment->id,
            'status' => $appointment->status,
            'start_time' => Carbon::parse($appointment->start_time)->toDateTimeString(),
            'end_time' => Carbon::parse($appointment->end_time)->toDateTimeString(),
            'patient' => $appointment->patient->user->name,
            'doctor' => $appointment->doctor->user->name,
            'speciality' => $appointment->reason->speciality->name,
            'reason' => $appointment->reason->name,
            'notes' => $appointment->notes,
        ]);
    }

    public function confirm(Appointment $appointment)
    {
        if ($appointment->status !== 'pending') {
            return back()->withErrors([
                'action' => 'Solo se pueden confirmar citas en estado pendiente.',
            ]);
        }

        $appointment->update(['status' => 'confirmed']);

        return back()->with('success', 'Cita confirmada exitosamente.');
    }

    public function cancel(Appointment $appointment)
    {
        if (!in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return back()->withErrors([
                'action' => 'Solo se pueden cancelar citas pendientes o confirmadas.',
            ]);
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Cita cancelada exitosamente.');
    }

    public function reprogram(Request $request, Appointment $appointment)
    {
        if ($appointment->status === 'confirmed') {
            return back()->withErrors([
                'action' => 'Una cita confirmada debe cancelarse primero para reprogramarla.',
            ]);
        }

        if ($appointment->status === 'completed') {
            return back()->withErrors([
                'action' => 'No se puede reprogramar una cita completada.',
            ]);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i',
            'doctor_id' => 'nullable|exists:doctors,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->service->reprogramAppointment(
                $appointment,
                $appointment->appointment_reason_id,
                $validated['date'],
                $validated['time_from'],
                $validated['time_to'],
                $validated['doctor_id'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['availability' => $e->getMessage()]);
        } catch (\Throwable) {
            return back()->withErrors(['availability' => 'No hay disponibilidad en el rango seleccionado.']);
        }

        return back()->with('success', 'Cita reprogramada exitosamente.');
    }
}
