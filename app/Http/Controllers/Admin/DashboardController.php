<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Speciality;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from) : now()->subDays(30);
        $to   = $request->to   ? Carbon::parse($request->to)   : now();

        // KPIs
        $kpis = [
            'total_patients'    => Patient::count(),
            'total_doctors'     => Doctor::count(),
            'total_appointments'=> Appointment::whereBetween('start_time', [$from, $to])->count(),
            'pending'           => Appointment::whereBetween('start_time', [$from, $to])->where('status', 'pending')->count(),
            'confirmed'         => Appointment::whereBetween('start_time', [$from, $to])->where('status', 'confirmed')->count(),
            'cancelled'         => Appointment::whereBetween('start_time', [$from, $to])->where('status', 'cancelled')->count(),
        ];

        $kpis['cancellation_rate'] = $kpis['total_appointments'] > 0
            ? round(($kpis['cancelled'] / $kpis['total_appointments']) * 100, 1)
            : 0;

        // Citas por día (para gráfico de línea)
        $byDay = Appointment::whereBetween('start_time', [$from, $to])
            ->selectRaw('DATE(start_time) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'total' => $r->total]);

        // Citas por especialidad (para gráfico de torta)
        $bySpeciality = Appointment::whereBetween('start_time', [$from, $to])
            ->join('appointment_reasons', 'appointments.appointment_reason_id', '=', 'appointment_reasons.id')
            ->join('specialities', 'appointment_reasons.speciality_id', '=', 'specialities.id')
            ->selectRaw('specialities.name as speciality, COUNT(*) as total')
            ->groupBy('specialities.name')
            ->get();

        // Citas por médico (para gráfico de barras)
        $byDoctor = Appointment::whereBetween('start_time', [$from, $to])
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->selectRaw('users.name as doctor, COUNT(*) as total')
            ->groupBy('users.name')
            ->get();

        return inertia('Admin/Dashboard', compact('kpis', 'byDay', 'bySpeciality', 'byDoctor'));
    }
}
