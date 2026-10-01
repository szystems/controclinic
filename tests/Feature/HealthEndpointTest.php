<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_hides_the_app_name_and_environment(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJsonPath('status', 'healthy');
        $response->assertJsonMissingPath('app');
        $response->assertJsonMissingPath('env');
    }

    public function test_health_stops_after_thirty_requests_per_minute(): void
    {
        foreach (range(1, 30) as $ignored) {
            $this->get('/health')->assertOk();
        }

        $this->get('/health')->assertStatus(429);
    }
}
