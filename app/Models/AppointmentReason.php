<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentReason extends Model
{
    protected $fillable = ['name', 'speciality_id'];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
