<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentReason;
use App\Models\Speciality;
use Illuminate\Http\Request;

class AppointmentReasonController extends Controller
{
    public function index()
    {
        $reasons = AppointmentReason::with('speciality')
            ->orderBy('name')
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'name'     => $r->name,
                'speciality' => [
                    'id'   => $r->speciality->id,
                    'name' => $r->speciality->name,
                ],
            ]);

        $specialities = Speciality::orderBy('name')
            ->get(['id', 'name']);

        return inertia('Admin/Reasons/Index', compact('reasons', 'specialities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'speciality_id' => 'required|exists:specialities,id',
        ], [
            'name.required'         => 'El nombre del motivo es obligatorio.',
            'speciality_id.required' => 'Debes seleccionar una especialidad.',
            'speciality_id.exists'   => 'La especialidad seleccionada no es válida.',
        ]);

        // Evitar duplicado del mismo motivo en la misma especialidad
        $exists = AppointmentReason::where('name', $request->name)
            ->where('speciality_id', $request->speciality_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'Ya existe ese motivo para la especialidad seleccionada.'
            ]);
        }

        AppointmentReason::create([
            'name'         => $request->name,
            'speciality_id' => $request->speciality_id,
        ]);

        return back()->with('success', 'Motivo de cita creado correctamente.');
    }

    public function update(Request $request, AppointmentReason $reason)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'speciality_id' => 'required|exists:specialities,id',
        ], [
            'name.required'         => 'El nombre del motivo es obligatorio.',
            'speciality_id.required' => 'Debes seleccionar una especialidad.',
        ]);

        // Evitar duplicado excluyendo el registro actual
        $exists = AppointmentReason::where('name', $request->name)
            ->where('speciality_id', $request->speciality_id)
            ->where('id', '!=', $reason->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'Ya existe ese motivo para la especialidad seleccionada.'
            ]);
        }

        $reason->update([
            'name'         => $request->name,
            'speciality_id' => $request->speciality_id,
        ]);

        return back()->with('success', 'Motivo actualizado correctamente.');
    }

    public function destroy(AppointmentReason $reason)
    {
        // Verificar si tiene citas asociadas
        if ($reason->appointments()->count() > 0) {
            return back()->withErrors([
                'delete' => 'No se puede eliminar: este motivo tiene citas registradas.'
            ]);
        }

        $reason->delete();

        return back()->with('success', 'Motivo de cita eliminado correctamente.');
    }
}
