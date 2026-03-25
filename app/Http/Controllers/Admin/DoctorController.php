<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // app/Http/Controllers/Admin/DoctorController.php

    public function index()
    {
        $doctors = Doctor::with(['user', 'specialities'])->get()->map(fn($d) => [
            'id'             => $d->id,
            'name'           => $d->user->name,
            'email'          => $d->user->email,
            'license_number' => $d->license_number,
            'bio'            => $d->bio,
            'active'         => $d->user->active,
            'specialities'    => $d->specialities->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
        ]);

        $specialities = Speciality::orderBy('name')->get(['id', 'name']);

        return inertia('Admin/Doctors/Index', compact('doctors', 'specialities'));
    }

    public function schedule(Doctor $doctor)
    {
        $doctor->load(['user', 'specialities']);

        $appointments = $doctor->appointments()
            ->with(['patient.user', 'reason.speciality'])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'start'        => $a->start_time,
                'end'          => $a->end_time,
                'status'       => $a->status,
                'patient_name' => $a->patient->user->name,
                'reason'       => $a->reason->name,
                'speciality'    => $a->reason->speciality->name,
                'notes'        => $a->notes,
            ]);

        return inertia('Admin/Doctors/Schedule', [
            'doctor' => [
                'id'          => $doctor->id,
                'name'        => $doctor->user->name,
                'specialities' => $doctor->specialities,
            ],
            'appointments' => $appointments,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Idea vaga del STORE
//        $exists = Appointment::where('doctor_id', $doctorId)
//            ->where('status', '!=', 'cancelled')
//            ->where(function ($q) use ($start, $end) {
//                $q->whereBetween('start_time', [$start, $end])
//                    ->orWhereBetween('end_time', [$start, $end])
//                    ->orWhere(function ($q2) use ($start, $end) {
//                        $q2->where('start_time', '<=', $start)
//                            ->where('end_time', '>=', $end);
//                    });
//            })->exists();
//
//        if ($exists) {
//            throw ValidationException::withMessages([
//                'time' => 'El médico ya tiene cita en ese horario.'
//            ]);
//        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }
}
