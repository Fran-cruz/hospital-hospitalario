<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Admin\{
    DashboardController,
    DoctorController,
    PatientController,
    SpecialityController,
    AppointmentReasonController,
    AppointmentController as AdminAppointmentController
};
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use Inertia\Inertia;

// ── Raíz ─────────────────────────────────────────────────────────────────
//Route::get('/', fn() => redirect('/login'));
// past redirect function
//Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// ── Redirección por rol tras login ────────────────────────────────────────
Route::middleware('auth')->get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'doctor'  => redirect()->route('doctor.appointments'),
        'patient' => redirect()->route('patient.appointments'),
//        default   => abort(403, 'Unauthorized action.')
        default    => redirect('/login'),
    };
});

// ── ADMIN ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Médicos + agenda
        Route::resource('doctors', DoctorController::class);
        Route::get('doctors/{doctor}/schedule', [DoctorController::class, 'schedule'])
            ->name('doctors.schedule');
        Route::patch('doctors/{doctor}', [DoctorController::class, 'update'])
            ->name('doctors.toggle'); // para active toggle

        // Pacientes (solo index + show)
        Route::resource('patients', PatientController::class)->only(['index', 'show']);

        // Especialidades CRUD
        Route::resource('specialities', SpecialityController::class)->except(['show']);

        // Motivos de cita CRUD
        Route::resource('reasons', AppointmentReasonController::class)->except(['show']);

        // Citas (solo lectura + filtros)
        Route::get('appointments',       [AdminAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
    });

// ── DOCTOR ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('appointments',                    [DoctorAppointmentController::class, 'index'])->name('appointments');
        Route::get('appointments/{appointment}',      [DoctorAppointmentController::class, 'show'])->name('appointments.show');
    });

// ── PACIENTE ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        // Historial
        Route::get('appointments',               [PatientAppointmentController::class, 'index'])->name('appointments');

        // Nueva cita — IMPORTANTE: /create antes de /{appointment}
        Route::get('appointments/create',        [PatientAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments',              [PatientAppointmentController::class, 'store'])->name('appointments.store');

        // Detalle
        Route::get('appointments/{appointment}', [PatientAppointmentController::class, 'show'])->name('appointments.show');

        // Acciones sobre cita
        Route::post('appointments/{appointment}/confirm',   [PatientAppointmentController::class, 'confirm'])->name('appointments.confirm');
        Route::post('appointments/{appointment}/cancel',    [PatientAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::post('appointments/{appointment}/reprogram', [PatientAppointmentController::class, 'reprogram'])->name('appointments.reprogram');
    });

