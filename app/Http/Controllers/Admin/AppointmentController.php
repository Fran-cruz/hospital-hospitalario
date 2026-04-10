<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user', 'reason.speciality'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->doctor_id, fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->from, fn($q) => $q->whereDate('start_time', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('start_time', '<=', $request->to))
            ->orderBy('start_time')
            ->get()
            ->map(fn($a) => [
                'id'            => $a->id,
                'start_time'    => $a->start_time ? Carbon::parse($a->start_time)->format('d/m/Y H:i') : null,
                'end_time'      => $a->end_time ? Carbon::parse($a->end_time)->format('d/m/Y H:i') : null,
                'start_raw'     => $a->start_time,                    // Para FullCalendar si lo necesitas
                'status'        => $a->status,
                'notes'        => $a->notes,
                'patient' => $a->patient->user->name,
                'doctor'  => $a->doctor->user->name,
                'speciality'   => $a->reason->speciality->name,
                'reason'       => $a->reason->name,
            ]);

        $doctors = Doctor::with('user')->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->user->name,
            ]);

        return inertia('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'doctors'      => $doctors,
            'filters'      => $request->only(['status', 'doctor_id', 'from', 'to']),
        ]);
    }

    /**
     * Cancel an existing appointment.
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $appointment->update(['status' => 'cancelled']);
        return back()->with('success', 'Cita cancelada exitosamente.');
    }

    /**
     * Reprogram an existing appointment.
     */
    public function reprogram(Request $request, Appointment $appointment)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        if ($this->hasConflict($appointment->doctor_id, $start, $end, $appointment->id)) {
            return back()->withErrors(['availability' => 'El médico ya tiene cita en ese horario.']);
        }

        $appointment->update([
            'start_time' => $start,
            'end_time' => $end,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Cita reprogramada exitosamente.');
    }

    private function hasConflict($doctorId, $start, $end, $appointmentId = null)
    {
        return Appointment::where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $appointmentId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            })->exists();
    }
}
