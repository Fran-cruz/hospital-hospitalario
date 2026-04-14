<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentReason;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AppointmentService
{
    private const WORK_START = 8;
    private const WORK_END = 17;
    private const SLOT_MINS = 30;
    private const MIN_RANGE_MINS = 60;

    public function findAvailableDoctorAndSlot(
        int $specialityId,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        ?int $doctorId = null,
        ?int $exceptAppointmentId = null
    ): ?array {
        $doctors = $this->getDoctorsForSpeciality($specialityId, $doctorId);
        if ($doctors->isEmpty()) {
            return null;
        }

        $slots = $this->generateSlots($rangeStart, $rangeEnd);
        if (empty($slots)) {
            return null;
        }

        foreach ($slots as [$slotStart, $slotEnd]) {
            foreach ($doctors as $doctor) {
                if (!$this->checkForConflicts($doctor->id, $slotStart, $slotEnd, $exceptAppointmentId)) {
                    return [
                        'doctor' => $doctor,
                        'slot' => [$slotStart->copy(), $slotEnd->copy()],
                    ];
                }
            }
        }

        return null;
    }

    public function getFirstAvailableSlot(
        Doctor $doctor,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        ?int $exceptAppointmentId = null
    ): ?array {
        $slots = $this->generateSlots($rangeStart, $rangeEnd);

        foreach ($slots as [$slotStart, $slotEnd]) {
            if (!$this->checkForConflicts($doctor->id, $slotStart, $slotEnd, $exceptAppointmentId)) {
                return [$slotStart->copy(), $slotEnd->copy()];
            }
        }

        return null;
    }

    public function checkForConflicts(
        int $doctorId,
        Carbon $start,
        Carbon $end,
        ?int $exceptAppointmentId = null
    ): bool {
        $query = Appointment::where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            });

        if (!is_null($exceptAppointmentId)) {
            $query->where('id', '!=', $exceptAppointmentId);
        }

        return $query->exists();
    }

    public function createPendingAppointment(
        $patient,
        int $reasonId,
        string $date,
        string $timeFrom,
        string $timeTo,
        ?int $doctorId = null
    ): Appointment {
        $reason = AppointmentReason::with('speciality')->findOrFail($reasonId);
        [$rangeStart, $rangeEnd] = $this->buildRange($date, $timeFrom, $timeTo);

        $result = $this->findAvailableDoctorAndSlot(
            $reason->speciality_id,
            $rangeStart,
            $rangeEnd,
            $doctorId
        );

        if (!$result) {
            throw new \RuntimeException('No hay disponibilidad en el rango seleccionado.');
        }

        [$start, $end] = $result['slot'];

        return Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $result['doctor']->id,
            'appointment_reason_id' => $reasonId,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $this->resolveInitialStatus($start),
        ]);
    }

    public function reprogramAppointment(
        Appointment $appointment,
        int $reasonId,
        string $date,
        string $timeFrom,
        string $timeTo,
        ?int $doctorId = null,
        ?string $notes = null
    ): Appointment {
        $reason = AppointmentReason::with('speciality')->findOrFail($reasonId);
        [$rangeStart, $rangeEnd] = $this->buildRange($date, $timeFrom, $timeTo);

        $result = $this->findAvailableDoctorAndSlot(
            $reason->speciality_id,
            $rangeStart,
            $rangeEnd,
            $doctorId,
            $appointment->id
        );

        if (!$result) {
            throw new \RuntimeException('No hay disponibilidad en el rango seleccionado.');
        }

        [$start, $end] = $result['slot'];

        $appointment->update([
            'doctor_id' => $result['doctor']->id,
            'appointment_reason_id' => $reasonId,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $this->resolveInitialStatus($start),
            'notes' => is_null($notes) ? $appointment->notes : $notes,
        ]);

        return $appointment->fresh();
    }

    public function getAvailabilityPreview(
        int $specialityId,
        string $date,
        string $timeFrom,
        string $timeTo,
        ?int $doctorId = null,
        ?int $exceptAppointmentId = null
    ): array {
        [$rangeStart, $rangeEnd] = $this->buildRange($date, $timeFrom, $timeTo);

        return $this->getDoctorsForSpeciality($specialityId, $doctorId)
            ->map(function ($doctor) use ($rangeStart, $rangeEnd, $exceptAppointmentId) {
                $slot = $this->getFirstAvailableSlot($doctor, $rangeStart, $rangeEnd, $exceptAppointmentId);

                return [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->user->name,
                    'first_slot' => $slot ? $slot[0]->toDateTimeString() : null,
                    'available' => !is_null($slot),
                ];
            })
            ->values()
            ->toArray();
    }

    private function resolveInitialStatus(Carbon $appointmentStart): string
    {
        $hoursUntilAppointment = now()->diffInHours($appointmentStart, false);
        return $hoursUntilAppointment <= 48 ? 'confirmed' : 'pending';
    }

    private function getDoctorsForSpeciality(int $specialityId, ?int $doctorId = null): Collection
    {
        return Doctor::with('user')
            ->when($doctorId, fn($query) => $query->where('id', $doctorId))
            ->whereHas('specialities', fn($query) => $query->where('speciality_id', $specialityId))
            ->whereHas('user', fn($query) => $query->where('active', true))
            ->orderBy('id')
            ->get();
    }

    private function buildRange(string $date, string $timeFrom, string $timeTo): array
    {
        try {
            $rangeStart = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$timeFrom}");
            $rangeEnd = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$timeTo}");
        } catch (\Throwable) {
            throw new InvalidArgumentException('El rango de fecha y hora no es válido.');
        }

        if (!$rangeStart->lt($rangeEnd)) {
            throw new InvalidArgumentException('La hora de inicio debe ser menor a la hora de fin.');
        }

        if ($rangeStart->diffInMinutes($rangeEnd) < self::MIN_RANGE_MINS) {
            throw new InvalidArgumentException('El rango mínimo debe ser de 1 hora.');
        }

        $workStart = $rangeStart->copy()->startOfDay()->setHour(self::WORK_START)->setMinute(0)->setSecond(0);
        $workEnd = $rangeStart->copy()->startOfDay()->setHour(self::WORK_END)->setMinute(0)->setSecond(0);

        if ($rangeStart->lt($workStart) || $rangeEnd->gt($workEnd)) {
            throw new InvalidArgumentException('El rango debe estar dentro del horario laboral (08:00 a 17:00).');
        }

        return [$rangeStart->copy(), $rangeEnd->copy()];
    }

    private function generateSlots(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $slots = [];
        $slotStart = $this->roundUpToSlot($rangeStart);

        while ($slotStart->copy()->addMinutes(self::SLOT_MINS)->lte($rangeEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes(self::SLOT_MINS);
            $slots[] = [$slotStart->copy(), $slotEnd->copy()];
            $slotStart->addMinutes(self::SLOT_MINS);
        }

        return $slots;
    }

    private function roundUpToSlot(Carbon $dateTime): Carbon
    {
        $rounded = $dateTime->copy()->setSecond(0);
        $minutes = (int) $rounded->minute;
        $next = (int) (ceil($minutes / self::SLOT_MINS) * self::SLOT_MINS);

        if ($next >= 60) {
            return $rounded->setMinute(0)->addHour();
        }

        return $rounded->setMinute($next);
    }
}
