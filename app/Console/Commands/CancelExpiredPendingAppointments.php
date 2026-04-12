<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

class CancelExpiredPendingAppointments extends Command
{
    protected $signature   = 'appointments:cancel-expired';
    protected $description = 'Cancela citas pendientes que ya entraron en las últimas 48 horas sin confirmar';

    public function handle(): void
    {
        // Buscar citas que:
        // 1. Estén en pending
        // 2. Sean futuras (no pasadas)
        // 3. start_time esté dentro de las próximas 48 horas (o menos)
        $toCancel = Appointment::where('status', 'pending')
            ->where('start_time', '>', now())
            ->where('start_time', '<=', now()->addHours(48))
            ->get();

        if ($toCancel->isEmpty()) {
            $this->info('No hay citas pendientes que cancelar.');
            return;
        }

        $count = 0;
        foreach ($toCancel as $appointment) {
            $appointment->update(['status' => 'cancelled']);
            $count++;
        }

        $this->info("{$count} cita(s) canceladas automáticamente por no confirmar a tiempo.");
    }
}
