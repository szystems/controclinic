<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class OpsQueuePingJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Cache::put('ops.queue_ping_at', now()->toIso8601String(), 120);
    }
}
