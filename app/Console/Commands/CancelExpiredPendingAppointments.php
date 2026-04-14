<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

class CancelExpiredPendingAppointments extends Command
{
    protected $signature = 'appointments:cancel-expired';
    protected $description = 'Actualiza citas vencidas y cancela pendientes no confirmadas en ventana de 48h';

    public function handle(): void
    {
        $completedCount = Appointment::where('status', 'confirmed')
            ->where('end_time', '<', now())
            ->update(['status' => 'completed']);

        $toCancel = Appointment::where('status', 'pending')
            ->where('start_time', '>', now())
            ->where('start_time', '<=', now()->addHours(48))
            ->get();

        $cancelledCount = 0;
        foreach ($toCancel as $appointment) {
            $appointment->update(['status' => 'cancelled']);
            $cancelledCount++;
        }

        if ($completedCount === 0 && $cancelledCount === 0) {
            $this->info('No hubo cambios en citas.');
            return;
        }

        $this->info("Citas completadas: {$completedCount}. Citas canceladas por no confirmar: {$cancelledCount}.");
    }
}
