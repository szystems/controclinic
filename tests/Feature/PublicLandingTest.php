<?php

namespace Tests\Feature;

use App\Models\Plan;
use Database\Seeders\PlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
    }
    // ==================== HOME ====================

    public function test_home_page_renders_ok(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Comenzar', false);
    }

    // ==================== PRICING ====================

    public function test_pricing_page_lists_active_public_plans(): void
    {
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('Solo', false)
            ->assertSee('Práctica', false)
            ->assertSee('Clínica', false)
            ->assertSee('Enterprise', false);
    }

    public function test_pricing_page_hides_private_plans(): void
    {
        Plan::create([
            'name' => 'PrivateOnlyPlan',
            'slug' => 'private-only-plan',
            'description' => 'hidden',
            'is_active' => true,
            'is_private' => true,
            'monthly_price' => 1.00,
            'yearly_price' => 10.00,
            'sort_order' => 50,
        ]);

        $this->get(route('pricing'))
            ->assertOk()
            ->assertDontSee('PrivateOnlyPlan', false);
    }

    public function test_pricing_page_hides_inactive_legacy_plans(): void
    {
        // El plan "group" se siembra como is_active=false + is_private=true.
        $this->get(route('pricing'))
            ->assertOk()
            ->assertDontSee('Group (legacy)', false);
    }

    public function test_pricing_displays_new_tier_amounts(): void
    {
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('$19', false)
            ->assertSee('$49', false)
            ->assertSee('$99', false);
    }

    public function test_practica_plan_is_marked_popular(): void
    {
        $practica = Plan::where('slug', 'practica')->first();

        $this->assertNotNull($practica);
        $this->assertTrue($practica->is_popular);
        $this->assertSame(49.00, (float) $practica->monthly_price);
        $this->assertSame(490.00, (float) $practica->yearly_price);
    }

    public function test_solo_plan_has_correct_limits(): void
    {
        $solo = Plan::where('slug', 'solo')->first();

        $this->assertNotNull($solo);
        $this->assertSame(1, $solo->max_doctors);
        $this->assertSame(1, $solo->max_staff);
        $this->assertSame(19.00, (float) $solo->monthly_price);
    }

    public function test_clinica_plan_has_correct_limits(): void
    {
        $clinica = Plan::where('slug', 'clinica')->first();

        $this->assertNotNull($clinica);
        $this->assertSame(8, $clinica->max_doctors);
        $this->assertSame(10, $clinica->max_staff);
        $this->assertSame(99.00, (float) $clinica->monthly_price);
    }

    // ==================== PLAN MODEL SCOPES ====================

    public function test_public_scope_excludes_private_plans(): void
    {
        Plan::create([
            'name' => 'Secret',
            'slug' => 'secret-x',
            'description' => 'hidden',
            'is_private' => true,
            'is_active' => true,
            'sort_order' => 51,
        ]);

        $publicSlugs = Plan::active()->public()->pluck('slug')->all();

        $this->assertContains('solo', $publicSlugs);
        $this->assertContains('practica', $publicSlugs);
        $this->assertContains('clinica', $publicSlugs);
        $this->assertNotContains('secret-x', $publicSlugs);
        $this->assertNotContains('group', $publicSlugs); // legacy → inactive
    }

    // ==================== CONTACT ====================

    public function test_contact_page_renders_ok(): void
    {
        $this->get(route('contact'))
            ->assertOk();
    }

    // ==================== SITEMAP / ROBOTS ====================

    public function test_sitemap_returns_xml_with_main_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type') ?? '');

        $xml = $response->getContent();
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString(route('home'), $xml);
        $this->assertStringContainsString(route('pricing'), $xml);
        $this->assertStringContainsString(route('contact'), $xml);
    }

    public function test_robots_txt_disallows_private_areas(): void
    {
        $path = public_path('robots.txt');
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertStringContainsString('Disallow: /app/', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }

    // ==================== LOCALE SWITCH ====================

    public function test_locale_switch_persists_in_session(): void
    {
        $this->get('/lang/en')->assertRedirect();
        $this->assertSame('en', session('locale'));

        $this->get('/lang/es')->assertRedirect();
        $this->assertSame('es', session('locale'));
    }

    public function test_locale_switch_ignores_invalid_locale(): void
    {
        $this->get('/lang/fr')->assertRedirect();
        $this->assertNotSame('fr', session('locale'));
    }
}
