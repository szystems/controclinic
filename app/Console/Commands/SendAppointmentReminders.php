<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders
                            {--hours=24 : Hours ahead to remind}
                            {--dry-run : Show count without dispatching}';

    protected $description = 'Dispatch reminder emails for appointments happening within N hours';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = (bool) $this->option('dry-run');

        // Ventana ancha en UTC para el primer filtro SQL — luego refinamos
        // por timezone de cada clínica.
        $from = now()->utc();
        $to = now()->utc()->addHours($hours);

        $query = Appointment::query()
            ->withoutGlobalScope('clinic') // El comando corre fuera de tenant
            ->with('clinic')
            ->whereIn('status', [
                Appointment::STATUS_SCHEDULED,
                Appointment::STATUS_CONFIRMED,
            ])
            ->where('reminder_sent', false)
            ->whereNotNull('appointment_date')
            ->whereBetween('appointment_date', [
                $from->copy()->subDay()->startOfDay(),
                $to->copy()->addDay()->endOfDay(),
            ]);

        // Filtro fino: cada cita se compara en la zona horaria de SU clínica
        $appointments = $query->get()->filter(function (Appointment $a) use ($hours) {
            $tz = $a->clinic?->timezone ?? config('app.timezone');

            $localNow = now($tz);
            $localTo = $localNow->copy()->addHours($hours);

            // appointment_date + start_time se interpretan en hora LOCAL de la clínica
            $apptLocal = Carbon::parse(
                $a->appointment_date->format('Y-m-d').' '.
                Carbon::parse($a->start_time)->format('H:i:s'),
                $tz
            );

            return $apptLocal->betweenIncluded($localNow, $localTo);
        });

        $count = $appointments->count();
        $this->info("Found {$count} appointment(s) to remind.");

        if ($dryRun || $count === 0) {
            return self::SUCCESS;
        }

        foreach ($appointments as $appointment) {
            SendAppointmentNotification::dispatch(
                $appointment->id,
                SendAppointmentNotification::TYPE_REMINDER,
            );
        }

        $this->info("Dispatched {$count} reminder job(s).");

        return self::SUCCESS;
    }
}
