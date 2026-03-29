<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speciality;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    public function index()
    {
        $specialities = Speciality::orderBy('name')
            ->withCount('appointmentReasons')
            ->get()
            ->map(fn($s) => [
                'id'                       => $s->id,
                'name'                     => $s->name,
                'appointment_reasons_count' => $s->appointment_reasons_count,
            ]);

        return inertia('Admin/Specialities/Index', compact('specialities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:specialities,name',
        ], [
            'name.required' => 'El nombre de la especialidad es obligatorio.',
            'name.unique'   => 'Ya existe una especialidad con ese nombre.',
        ]);

        Speciality::create(['name' => $request->name]);

        return back()->with('success', 'Especialidad creada correctamente.');
    }

    public function update(Request $request, Speciality $speciality)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:specialities,name,' . $speciality->id,
        ], [
            'name.required' => 'El nombre de la especialidad es obligatorio.',
            'name.unique'   => 'Ya existe una especialidad con ese nombre.',
        ]);

        $speciality->update(['name' => $request->name]);

        return back()->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Speciality $speciality)
    {
        // Verificar si tiene motivos asociados
        if ($speciality->appointmentReasons()->count() > 0) {
            return back()->withErrors([
                'delete' => 'No se puede eliminar: esta especialidad tiene motivos de cita asociados.'
            ]);
        }

        // Verificar si tiene médicos asociados
        if ($speciality->doctors()->count() > 0) {
            return back()->withErrors([
                'delete' => 'No se puede eliminar: hay médicos asignados a esta especialidad.'
            ]);
        }

        $speciality->delete();

        return back()->with('success', 'Especialidad eliminada correctamente.');
    }
}
