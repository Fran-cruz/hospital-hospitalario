<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['user', 'specialities'])->get()->map(fn($d) => [
            'id'             => $d->id,
            'name'           => $d->user->name,
            'email'          => $d->user->email,
            'license_number' => $d->license_number,
            'bio'            => $d->bio,
            'active'         => $d->user->active,
            'specialities'   => $d->specialities->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name,
            ]),
        ]);

        $specialities = Speciality::orderBy('name')->get(['id', 'name']);

        return inertia('Admin/Doctors/Index', compact('doctors', 'specialities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
            'license_number' => 'required|string|unique:doctors,license_number',
            'bio'            => 'nullable|string',
            'specialities'   => 'required|array|min:1',
            'specialities.*' => 'exists:specialities,id',
            'active'         => 'boolean',
        ], [
            'license_number.unique' => 'Ya existe un médico con ese número de licencia.',
            'specialities.required' => 'Debes asignar al menos una especialidad.',
            'email.unique'          => 'Este correo ya está registrado.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'doctor',
            'active'   => $request->boolean('active', true),
        ]);

        $doctor = Doctor::create([
            'user_id'        => $user->id,
            'license_number' => $request->license_number,
            'bio'            => $request->bio,
        ]);

        $doctor->specialities()->attach($request->specialities);

        return back()->with('success', 'Médico creado correctamente.');
    }

    public function update(Request $request, Doctor $doctor)
    {
        if ($request->has('active') && !$request->hasAny([
                'name', 'email', 'password', 'license_number', 'bio', 'specialities'
            ])) {
            $doctor->user->update([
                'active' => $request->boolean('active'),
            ]);

            return back()->with('success', 'Estado del médico actualizado.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctor->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'license_number' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'specialities' => ['required', 'array'],
            'specialities.*' => ['exists:specialities,id'],
            'active' => ['nullable', 'boolean'],
            ]);

        $doctor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'active' => $request->boolean('active'),
            ...(!empty($validated['password']) ? ['password' => bcrypt($validated['password'])] : []),
        ]);

        $doctor->update([
            'license_number' => $validated['license_number'],
            'bio' => $validated['bio'] ?? null,
        ]);

        $doctor->specialities()->sync($validated['specialities']);

        return back()->with('success', 'Médico actualizado correctamente.');
    }
    public function destroy(Doctor $doctor)
    {
        if ($doctor->appointments()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return back()->withErrors([
                'delete' => 'No se puede eliminar: el médico tiene citas activas.'
            ]);
        }

        $user = $doctor->user;
        $doctor->specialities()->detach();
        $doctor->delete();
        $user->delete();

        return back()->with('success', 'Médico eliminado correctamente.');
    }

    public function schedule(Doctor $doctor)
    {
        $doctor->load(['user', 'specialities']);

        $appointments = $doctor->appointments()
            ->with(['patient.user', 'reason.speciality'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'start'        => $a->start_time,
                'end'          => $a->end_time,
                'status'       => $a->status,
                'patient_name' => $a->patient->user->name,
                'reason'       => $a->reason->name,
                'speciality'   => $a->reason->speciality->name,
                'notes'        => $a->notes,
            ]);

        return inertia('Admin/Doctors/Schedule', [
            'doctor' => [
                'id'          => $doctor->id,
                'name'        => $doctor->user->name,
                'specialities'=> $doctor->specialities,
            ],
            'appointments' => $appointments,
        ]);
    }
}
