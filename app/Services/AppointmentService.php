<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\AppointmentReason;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AppointmentService
{
    // Horario laboral de la clínica
    const WORK_START = 8;   // 08:00
    const WORK_END   = 17;  // 17:00 (última cita 16:30)
    const SLOT_MINS  = 30;

    /**
     * Encuentra un médico disponible al azar y devuelve el primer slot libre.
     * Retorna: ['doctor' => Doctor, 'slot' => [Carbon $start, Carbon $end]] | null
     */
    public function findAvailableDoctorAndSlot(int $specialityId, string $from, string $to): ?array
    {
        $rangeStart = Carbon::parse($from);
        $rangeEnd   = Carbon::parse($to);

        $doctors = Doctor::whereHas('specialities', fn($q) =>
        $q->where('speciality_id', $specialityId)
        )->with('user')->get();

        // Filtramos los que tienen al menos un slot libre
        $available = $doctors->filter(fn($doc) =>
            $this->getFirstAvailableSlot($doc, $rangeStart, $rangeEnd) !== null
        );

        if ($available->isEmpty()) {
            return null;
        }

        // Random entre disponibles
        $doctor = $available->random();
        $slot   = $this->getFirstAvailableSlot($doctor, $rangeStart, $rangeEnd);

        return compact('doctor', 'slot');
    }

    /**
     * Devuelve el primer slot [Carbon $start, Carbon $end] libre del médico
     * dentro del rango dado, respetando horario laboral.
     */
    public function getFirstAvailableSlot(Doctor $doctor, Carbon $from, Carbon $to): ?array
    {
        // Iteramos día a día dentro del rango
        $period = CarbonPeriod::create(
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay()
        );

        foreach ($period as $day) {
            // Saltar fines de semana (opcional — quita estas 2 líneas si la clínica trabaja sábados)
            if ($day->isWeekend()) continue;

            $slotStart = $day->copy()->setHour(self::WORK_START)->setMinute(0)->setSecond(0);
            $dayEnd    = $day->copy()->setHour(self::WORK_END)->setMinute(0)->setSecond(0);

            // Si el rango empieza en el medio del día, ajustar
            if ($from->isSameDay($day) && $from->gt($slotStart)) {
                // Redondear al siguiente slot de 30 min
                $mins      = ceil($from->minute / 30) * 30;
                $slotStart = $from->copy()->setMinute(0)->setSecond(0)->addMinutes($mins);
            }

            while ($slotStart->copy()->addMinutes(self::SLOT_MINS)->lte($dayEnd)) {
                $slotEnd = $slotStart->copy()->addMinutes(self::SLOT_MINS);

                if (!$this->checkForConflicts($doctor->id, $slotStart, $slotEnd)) {
                    return [$slotStart->copy(), $slotEnd->copy()];
                }

                $slotStart->addMinutes(self::SLOT_MINS);
            }
        }

        return null;
    }

    /**
     * Verifica si existe alguna cita que choque con el rango dado para ese médico.
     */
    public function checkForConflicts(int $doctorId, Carbon $start, Carbon $end): bool
    {
        return Appointment::where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->where(fn($q1) =>
                $q1->where('start_time', '<', $end)
                    ->where('end_time', '>', $start)
                );
            })
            ->exists();
    }

    /**
     * Crea la cita pendiente completa.
     */
    public function createPendingAppointment($patient, int $reasonId, string $from, string $to): Appointment
    {
        $reason = AppointmentReason::with('speciality')->findOrFail($reasonId);

        $result = $this->findAvailableDoctorAndSlot($reason->speciality_id, $from, $to);

        if (!$result) {
            throw new \Exception('No hay disponibilidad en el rango seleccionado.');
        }

        [$start, $end] = $result['slot'];

        return Appointment::create([
            'patient_id'            => $patient->id,
            'doctor_id'             => $result['doctor']->id,
            'appointment_reason_id' => $reasonId,
            'start_time'            => $start,
            'end_time'              => $end,
            'status'                => 'pending',
        ]);
    }

    /**
     * Devuelve todos los slots libres de todos los médicos de una especialidad.
     * Útil para el endpoint /api/availability (preview en frontend).
     */
    public function getAvailabilityPreview(int $specialityId, string $from, string $to): array
    {
        $rangeStart = Carbon::parse($from);
        $rangeEnd   = Carbon::parse($to);

        $doctors = Doctor::whereHas('specialities', fn($q) =>
        $q->where('speciality_id', $specialityId)
        )->with('user')->get();

        return $doctors->map(function ($doctor) use ($rangeStart, $rangeEnd) {
            $slot = $this->getFirstAvailableSlot($doctor, $rangeStart, $rangeEnd);

            return [
                'doctor_id'   => $doctor->id,
                'doctor_name' => $doctor->user->name,
                'first_slot'  => $slot ? $slot[0]->toDateTimeString() : null,
                'available'   => !is_null($slot),
            ];
        })->values()->toArray();
    }
}
