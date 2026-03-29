<?php

use App\Http\Controllers\Api\AppointmentApiController;
use Illuminate\Support\Facades\Route;

// Usar middleware web (sesión) en lugar de sanctum
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/availability', [AppointmentApiController::class, 'availability']);
});
