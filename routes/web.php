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
//Route::get('/', fn() => redirect('login'));
Route::get('/', fn() => Inertia::render('Landing'));

// ── Redirección por rol tras login ────────────────────────────────────────
Route::middleware('auth')->get('/dashboard', function () {
    if (!auth()->check()) {
        abort(403, 'Unauthorized action.');
    }

    return match (auth()->user()->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'doctor'  => redirect()->route('doctor.appointments'),
        'patient' => redirect()->route('patient.appointments'),
//        default    => redirect('login'),
    };
})->name('dashboard');

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
        Route::resource('specialities', SpecialityController::class)
            ->except(['show', 'create', 'edit']);

        // Motivos — CRUD completo excepto show
        Route::resource('reasons', AppointmentReasonController::class)
            ->except(['show', 'create', 'edit']);

        // Citas (solo lectura + filtros)
        Route::get('appointments',       [AdminAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
        Route::post('appointments/{appointment}/confirm', [AdminAppointmentController::class, 'confirm'])->name('appointments.confirm');
        Route::post('appointments/{appointment}/cancel', [AdminAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::post('appointments/{appointment}/reprogram', [AdminAppointmentController::class, 'reprogram'])->name('appointments.reprogram');
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
        Route::get('appointments', [PatientAppointmentController::class, 'index'])
            ->name('appointments');

        // IMPORTANTE: /create debe ir ANTES de /{appointment}
        Route::get('appointments/create', [PatientAppointmentController::class, 'create'])
            ->name('appointments.create');
        Route::post('appointments', [PatientAppointmentController::class, 'store'])
            ->name('appointments.store');

        // Detalle
        Route::get('appointments/{appointment}', [PatientAppointmentController::class, 'show'])
            ->name('appointments.show');

        // Acciones
        Route::post('appointments/{appointment}/confirm', [PatientAppointmentController::class, 'confirm'])
            ->name('appointments.confirm');
        Route::post('appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::post('appointments/{appointment}/reprogram', [PatientAppointmentController::class, 'reprogram'])
            ->name('appointments.reprogram');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/api/availability', [
        \App\Http\Controllers\Api\AppointmentApiController::class,
        'availability'
    ])->name('api.availability');
});
