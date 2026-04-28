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
