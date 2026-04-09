<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentReason;
use App\Models\Doctor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AppointmentService
{
    const WORK_START = 8;
    const WORK_END   = 17;
    const SLOT_MINS  = 30;

    public function findAvailableDoctorAndSlot(
        int $specialityId,
        string $from,
        string $to
    ): ?array {
        $rangeStart = Carbon::parse($from);
        $rangeEnd   = Carbon::parse($to);

        $doctors = Doctor::with('user')
            ->whereHas('specialities', fn($q) =>
            $q->where('speciality_id', $specialityId)
            )
            ->whereHas('user', fn($q) =>
            $q->where('active', true)
            )
            ->get();

        $available = $doctors->filter(fn($doc) =>
            $this->getFirstAvailableSlot($doc, $rangeStart, $rangeEnd) !== null
        );

        if ($available->isEmpty()) return null;

        $doctor = $available->random();
        $slot   = $this->getFirstAvailableSlot($doctor, $rangeStart, $rangeEnd);

        return compact('doctor', 'slot');
    }

    public function getFirstAvailableSlot(
        Doctor $doctor,
        Carbon $from,
        Carbon $to
    ): ?array {
        $period = CarbonPeriod::create(
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay()
        );

        foreach ($period as $day) {
            if ($day->isWeekend()) continue;

            $slotStart = $day->copy()->setHour(self::WORK_START)->setMinute(0)->setSecond(0);
            $dayEnd    = $day->copy()->setHour(self::WORK_END)->setMinute(0)->setSecond(0);

            if ($from->isSameDay($day) && $from->gt($slotStart)) {
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

    public function checkForConflicts(
        int $doctorId,
        Carbon $start,
        Carbon $end
    ): bool {
        return Appointment::where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->where(fn($q) =>
            $q->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
            )
            ->exists();
    }

    /**
     * Si la cita es en menos de 48 horas → confirmed
     * Si es en más de 48 horas → pending (el paciente tiene hasta
     * las últimas 48h para confirmar, después se cancela por el comando)
     */
    private function resolveInitialStatus(Carbon $appointmentStart): string
    {
        $hoursUntilAppointment = now()->diffInHours($appointmentStart, false);
        return $hoursUntilAppointment <= 48 ? 'confirmed' : 'pending';
    }

    public function createPendingAppointment(
        $patient,
        int $reasonId,
        string $from,
        string $to
    ): Appointment {
        $reason = AppointmentReason::with('speciality')->findOrFail($reasonId);

        $result = $this->findAvailableDoctorAndSlot(
            $reason->speciality_id,
            $from,
            $to
        );

        if (!$result) {
            throw new \Exception('No hay disponibilidad en el rango seleccionado.');
        }

        [$start, $end] = $result['slot'];

        // === LÓGICA CORRECTA DE 48 HORAS ===
        $status = $this->resolveInitialStatus($start);

        return Appointment::create([
            'patient_id'            => $patient->id,
            'doctor_id'             => $result['doctor']->id,
            'appointment_reason_id' => $reasonId,
            'start_time'            => $start,
            'end_time'              => $end,
            'status'                => $status,
        ]);
    }

    /**
     * Calcula si la cita debe crearse como confirmada o pendiente
     * Basado en exactamente 48 horas desde AHORA
     */

    public function getAvailabilityPreview(
        int $specialityId,
        string $from,
        string $to
    ): array {
        $rangeStart = Carbon::parse($from);
        $rangeEnd   = Carbon::parse($to);

        $doctors = Doctor::with('user')
            ->whereHas('specialities', fn($q) =>
            $q->where('speciality_id', $specialityId)
            )
            ->whereHas('user', fn($q) =>
            $q->where('active', true)
            )
            ->get();

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
