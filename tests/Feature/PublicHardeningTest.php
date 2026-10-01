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

    public function test_marketing_footer_only_links_to_real_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('public.footer_features'), false)
            ->assertSee(__('public.footer_pricing'), false)
            ->assertSee(__('public.footer_contact'), false)
            ->assertDontSee('href="#"', false)
            ->assertDontSee('Integraciones', false)
            ->assertDontSee('Actualizaciones', false)
            ->assertDontSee('Centro de Ayuda', false)
            ->assertDontSee('Guías', false)
            ->assertDontSee('>Blog<', false);
    }
}
