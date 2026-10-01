<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_hides_private_areas_and_points_at_this_site(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /app/', $robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);
        $this->assertStringContainsString('Disallow: /appointment/', $robots);
        $this->assertStringContainsString('Disallow: /invitation/', $robots);
        $this->assertStringContainsString('Disallow: /lang/', $robots);
        $this->assertStringContainsString('Allow: /c/', $robots);
        $this->assertStringContainsString('Sitemap: https://controclinic.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString('app.controclinic.com', $robots);
    }
}
