<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_pin_the_alpine_build(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('alpinejs@3.14.9/dist/cdn.min.js', false)
            ->assertSee('integrity="sha384-9Ax3MmS9AClxJyd5/zafcXXjxmwFhZCdsT6HJoJjarvCaAkJlk5QDzjLJm+Wdx5F"', false)
            ->assertDontSee('alpinejs@3.x.x', false);
    }
}
