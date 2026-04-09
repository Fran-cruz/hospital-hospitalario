<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|lowercase|email|max:255|unique:users',
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'                 => 'nullable|string|max:20',
            'birth_date'            => 'nullable|date|before:today',
            'gender'                => 'nullable|in:M,F,O',
        ], [
            'name.required'         => 'El nombre es obligatorio.',
            'email.required'        => 'El correo electrónico es obligatorio.',
            'email.unique'          => 'Este correo ya está registrado.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
            'birth_date.before'     => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.in'             => 'El género seleccionado no es válido.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'patient',
            'active'   => true,
        ]);

        // Crear automáticamente el perfil del paciente
        Patient::create([
            'user_id'    => $user->id,
            'phone'      => $request->phone,
            'birth_date' => $request->birth_date,
            'gender'     => $request->gender,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('patient.appointments');
    }
}
