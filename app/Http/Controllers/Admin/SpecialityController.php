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
            ->map(fn($speciality) => [
                'id' => $speciality->id,
                'name' => $speciality->name,
                'appointment_reasons_count' => $speciality->appointment_reasons_count,
            ]);

        return inertia('Admin/Specialities/Index', compact('specialities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:specialities,name',
        ], [
            'name.required' => 'El nombre de la especialidad es obligatorio.',
            'name.unique' => 'Ya existe una especialidad con ese nombre.',
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
            'name.unique' => 'Ya existe una especialidad con ese nombre.',
        ]);

        $speciality->update(['name' => $request->name]);

        return back()->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Speciality $speciality)
    {
        // Se mantiene esta regla funcional: si hay medicos asignados, no se elimina.
        if ($speciality->doctors()->exists()) {
            return back()->withErrors([
                'delete' => 'No se puede eliminar: hay medicos asignados a esta especialidad.',
            ]);
        }

        // Cascada en base de datos:
        // specialities -> appointment_reasons -> appointments.
        $speciality->delete();

        return back()->with('success', 'Especialidad eliminada correctamente.');
    }
}
