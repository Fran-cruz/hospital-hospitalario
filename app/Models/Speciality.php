<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    protected $fillable = ['name'];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class);
    }

    public function appointmentReasons()
    {
        return $this->hasMany(AppointmentReason::class);
    }
}
