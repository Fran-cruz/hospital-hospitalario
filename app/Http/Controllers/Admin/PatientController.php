<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // app/Http/Controllers/Admin/PatientController.php

    public function index()
    {
        $patients = Patient::with('user')
            ->withCount('appointments')
            ->get()
            ->map(fn($p) => [
                'id'                  => $p->id,
                'name'                => $p->user->name,
                'email'               => $p->user->email,
                'phone'               => $p->phone,
                'birth_date'          => $p->birth_date,
                'gender'              => $p->gender,
                'appointments_count'  => $p->appointments_count,
            ]);

        return inertia('Admin/Patients/Index', compact('patients'));
    }

    public function show(Patient $patient)
    {
        $patient->load(['user', 'appointments.doctor.user', 'appointments.reason.speciality']);

        $data = [
            'id'         => $patient->id,
            'name'       => $patient->user->name,
            'email'      => $patient->user->email,
            'phone'      => $patient->phone,
            'birth_date' => $patient->birth_date,
            'gender'     => $patient->gender,
            'appointments' => $patient->appointments->map(fn($a) => [
                'id'         => $a->id,
                'start_time' => $a->start_time,
                'status'     => $a->status,
                'doctor'     => $a->doctor->user->name,
                'speciality'  => $a->reason->speciality->name,
                'reason'     => $a->reason->name,
            ]),
        ];

        return inertia('Admin/Patients/Show', ['patient' => $data]);
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
