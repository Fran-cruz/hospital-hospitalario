<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // Idea vaga del método STORE
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
//                'time' => 'El médico ya tiene una cita en ese horario.'
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
