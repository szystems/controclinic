<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_and_failed_jobs_are_pruned_on_a_schedule(): void
    {
        $commands = collect(app(Schedule::class)->events())
            ->map(fn ($event) => (string) $event->command)
            ->implode("\n");

        $this->assertStringContainsString('activitylog:clean --days=730', $commands);
        $this->assertStringContainsString('queue:prune-failed --hours=168', $commands);
    }
}
