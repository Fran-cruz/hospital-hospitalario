<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_reason_id',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    // Funciones para las Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function reason()
    {
        return $this->belongsTo(AppointmentReason::class, 'appointment_reason_id');
    }
}
