<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentReason extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentReasonFactory> */
    use HasFactory;

    // Funciones para las Relaciones
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
