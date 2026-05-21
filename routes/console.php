<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send reminders for appointments happening within the next 24h, every hour
Schedule::command('appointments:send-reminders --hours=24')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// ─── Backup automático ────────────────────────────────────────────────────────
// Limpiar backups antiguos según política de retención (config/backup.php)
Schedule::command('backup:clean')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// Ejecutar backup completo de BD a las 02:00 AM
Schedule::command('backup:run --only-db')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->sendOutputTo(storage_path('logs/backup.log'));
