<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorFactory> */
    use HasFactory;

    // Funciones para las Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function specialities()
    {
        return $this->belongsToMany(Speciality::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
