<?php

namespace App\Console\Commands;

use App\Jobs\OpsQueuePingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class OpsHealthCommand extends Command
{
    protected $signature = 'ops:health
                            {--queue : Dispatch a queue ping job and verify the worker processes it}
                            {--mail= : Send a test email to this address}';

    protected $description = 'Verify production ops: database, redis, mail, storage, queue worker, scheduler';

    public function handle(): int
    {
        $failed = false;

        $this->components->info('ControClinic ops health check');
        $this->newLine();

        $failed = $this->checkDatabase() || $failed;
        $failed = $this->checkRedis() || $failed;
        $failed = $this->checkStorage() || $failed;
        $failed = $this->checkMail() || $failed;
        $failed = $this->checkScheduler() || $failed;

        if ($this->option('queue')) {
            $failed = $this->checkQueueWorker() || $failed;
        }

        if ($email = $this->option('mail')) {
            $failed = $this->call('mail:test', ['email' => $email]) !== self::SUCCESS || $failed;
        }

        $this->newLine();

        if ($failed) {
            $this->components->error('One or more checks failed.');

            return self::FAILURE;
        }

        $this->components->info('All checks passed.');

        return self::SUCCESS;
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->line('  <fg=green>✓</> Database connection');

            return false;
        } catch (\Throwable $e) {
            $this->line('  <fg=red>✗</> Database: '.$e->getMessage());

            return true;
        }
    }

    private function checkRedis(): bool
    {
        try {
            Redis::ping();
            $this->line('  <fg=green>✓</> Redis connection');

            return false;
        } catch (\Throwable $e) {
            $this->line('  <fg=red>✗</> Redis: '.$e->getMessage());

            return true;
        }
    }

    private function checkStorage(): bool
    {
        $failed = false;

        if (! is_link(public_path('storage'))) {
            $this->line('  <fg=red>✗</> storage:link missing (public/storage)');
            $failed = true;
        } else {
            $this->line('  <fg=green>✓</> public/storage symlink');
        }

        try {
            Storage::disk(config('filesystems.default'))->put('ops-health-check.txt', 'ok');
            Storage::disk(config('filesystems.default'))->delete('ops-health-check.txt');
            $this->line('  <fg=green>✓</> Storage disk writable');
        } catch (\Throwable $e) {
            $this->line('  <fg=red>✗</> Storage: '.$e->getMessage());
            $failed = true;
        }

        return $failed;
    }

    private function checkMail(): bool
    {
        $driver = config('mail.default');
        $from = config('mail.from.address');

        if ($driver === 'log') {
            $this->line('  <fg=yellow>!</> Mail driver is "log" (emails not sent externally)');

            return true;
        }

        $this->line("  <fg=green>✓</> Mail driver [{$driver}] · from {$from}");

        return false;
    }

    private function checkScheduler(): bool
    {
        $lastRun = Cache::get('ops.scheduler_last_run');

        if (! $lastRun) {
            $this->line('  <fg=yellow>!</> Scheduler heartbeat not seen yet (wait ~2 min after deploy)');

            return true;
        }

        $age = now()->diffInSeconds(Carbon::parse($lastRun));

        if ($age > 180) {
            $this->line("  <fg=red>✗</> Scheduler stale — last heartbeat {$age}s ago ({$lastRun})");

            return true;
        }

        $this->line("  <fg=green>✓</> Scheduler heartbeat ({$age}s ago)");

        return false;
    }

    private function checkQueueWorker(): bool
    {
        Cache::forget('ops.queue_ping_at');
        OpsQueuePingJob::dispatch();

        $this->line('  … Queue ping job dispatched, waiting for worker...');

        for ($i = 0; $i < 15; $i++) {
            sleep(1);

            if (Cache::get('ops.queue_ping_at')) {
                $this->line('  <fg=green>✓</> Queue worker processed ping job');

                return false;
            }
        }

        $this->line('  <fg=red>✗</> Queue worker did not process job within 15s');

        return true;
    }
}
