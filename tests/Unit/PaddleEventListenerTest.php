<?php

namespace Tests\Unit;

use App\Listeners\PaddleEventListener;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaddleEventListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_plan_from_price_returns_correct_plan(): void
    {
        // Create plans in DB
        Plan::create(['name' => 'Solo', 'slug' => 'solo', 'paddle_monthly_price_id' => 'pri_solo_monthly_test', 'paddle_yearly_price_id' => 'pri_solo_yearly_test', 'sort_order' => 1]);
        Plan::create(['name' => 'Group', 'slug' => 'group', 'paddle_monthly_price_id' => 'pri_group_monthly_test', 'paddle_yearly_price_id' => 'pri_group_yearly_test', 'sort_order' => 2]);

        // Set test price IDs in config as fallback
        config(['cashier.prices' => [
            'solo' => [
                'monthly' => 'pri_solo_monthly_test',
                'yearly' => 'pri_solo_yearly_test',
            ],
            'group' => [
                'monthly' => 'pri_group_monthly_test',
                'yearly' => 'pri_group_yearly_test',
            ],
        ]]);

        $listener = new PaddleEventListener;
        $method = new \ReflectionMethod($listener, 'resolvePlanFromPrice');

        // Create a mock subscription with items
        $mockItem = new \stdClass;
        $mockItem->price_id = 'pri_solo_monthly_test';

        $mockItems = collect([$mockItem]);

        $mockSubscription = new class($mockItems)
        {
            public $items;

            public function __construct($items)
            {
                $this->items = $items;
            }
        };

        $result = $method->invoke($listener, $mockSubscription);
        $this->assertInstanceOf(Plan::class, $result);
        $this->assertEquals('solo', $result->slug);

        // Test group plan
        $mockItem->price_id = 'pri_group_yearly_test';
        $result = $method->invoke($listener, $mockSubscription);
        $this->assertInstanceOf(Plan::class, $result);
        $this->assertEquals('group', $result->slug);

        // Test unknown price
        $mockItem->price_id = 'pri_unknown';
        $result = $method->invoke($listener, $mockSubscription);
        $this->assertNull($result);
    }

    public function test_resolve_plan_returns_null_for_empty_items(): void
    {
        $listener = new PaddleEventListener;
        $method = new \ReflectionMethod($listener, 'resolvePlanFromPrice');

        $mockSubscription = new class
        {
            public $items;

            public function __construct()
            {
                $this->items = collect([]);
            }
        };

        $result = $method->invoke($listener, $mockSubscription);
        $this->assertNull($result);
    }
}
