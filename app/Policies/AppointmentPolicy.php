<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'doctor') {
            return $user->doctor
                && $appointment->doctor_id === $user->doctor->id;
        }

        if ($user->role === 'patient') {
            return $user->patient
                && $appointment->patient_id === $user->patient->id;
        }

        return false;
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->role === 'patient'
            && $user->patient
            && $appointment->patient_id === $user->patient->id
            && $appointment->status === 'pending';
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->role === 'patient'
            && $user->patient
            && $appointment->patient_id === $user->patient->id
            && in_array($appointment->status, ['pending', 'confirmed']);
    }

    public function reprogram(User $user, Appointment $appointment): bool
    {
        return $this->cancel($user, $appointment);
    }
}
