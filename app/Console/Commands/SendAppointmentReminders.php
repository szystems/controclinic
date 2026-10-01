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
        $scanHours = max($hours, 168);

        // Ventana ancha en UTC para el primer filtro SQL — luego refinamos
        // por timezone y por las horas configuradas en cada clínica.
        $from = now()->utc();
        $to = now()->utc()->addHours($scanHours);

        $query = Appointment::query()
            ->withoutGlobalScope('clinic') // El comando corre fuera de tenant
            ->with(['clinic', 'patient'])
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
            try {
                $settings = $a->clinic?->settings ?? [];
                if (array_key_exists('send_reminders', $settings) && ! $settings['send_reminders']) {
                    return false;
                }

                $clinicHours = isset($settings['reminder_hours_before'])
                    ? (int) $settings['reminder_hours_before']
                    : $hours;
                if ($clinicHours < 1) {
                    $clinicHours = $hours;
                }

                $tz = $a->clinic?->timezone ?? config('app.timezone');

                $localNow = now($tz);
                $localTo = $localNow->copy()->addHours($clinicHours);

                // appointment_date + start_time se interpretan en hora LOCAL de la clínica
                $apptLocal = Carbon::parse(
                    $a->appointment_date->format('Y-m-d').' '.
                    Carbon::parse($a->start_time)->format('H:i:s'),
                    $tz
                );

                return $apptLocal->betweenIncluded($localNow, $localTo);
            } catch (\Throwable $e) {
                report($e);

                return false;
            }
        });

        $count = $appointments->count();
        $this->info("Found {$count} appointment(s) to remind.");

        if ($dryRun || $count === 0) {
            return self::SUCCESS;
        }

        $dispatched = 0;

        foreach ($appointments as $appointment) {
            $claimed = Appointment::withoutGlobalScope('clinic')
                ->whereKey($appointment->id)
                ->where('reminder_sent', false)
                ->update([
                    'reminder_sent' => true,
                    'reminder_sent_at' => now(),
                ]);

            if ($claimed !== 1) {
                continue;
            }

            $patient = $appointment->patient;
            if (! $patient || ! $patient->email || $patient->trashed()) {
                continue;
            }

            SendAppointmentNotification::dispatch(
                $appointment->id,
                SendAppointmentNotification::TYPE_REMINDER,
            );
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} reminder job(s).");

        return self::SUCCESS;
    }
}
