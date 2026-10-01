<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_ignores_an_external_referer(): void
    {
        $this->withHeader('Referer', 'https://evil.example/phish')
            ->get('/lang/es')
            ->assertRedirect(route('home'));
    }

    public function test_language_switch_returns_to_the_same_site(): void
    {
        $this->withHeader('Referer', 'http://localhost/login')
            ->get('/lang/en')
            ->assertRedirect('http://localhost/login');
    }
}
