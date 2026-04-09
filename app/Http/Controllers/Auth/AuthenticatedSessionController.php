<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();
        if (! $user->active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }
        // Redirigir según rol
        $role = Auth::user()->role;
        return match ($role) {
            'admin'   => redirect()->intended(route('admin.dashboard')),
            'doctor'  => redirect()->intended(route('doctor.appointments')),
            'patient' => redirect()->intended(route('patient.appointments')),
            default   => redirect()->intended(route('patient.appointments')),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
