<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Laravel\Paddle\Events\SubscriptionCanceled;
use Laravel\Paddle\Events\SubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionUpdated;
use Tests\TestCase;

class PaddleListenerRegistrationTest extends TestCase
{
    public function test_each_paddle_subscription_event_has_one_listener(): void
    {
        foreach ([
            SubscriptionCreated::class => 'handleSubscriptionCreated',
            SubscriptionUpdated::class => 'handleSubscriptionUpdated',
            SubscriptionCanceled::class => 'handleSubscriptionCanceled',
        ] as $event => $method) {
            Artisan::call('event:list', ['--event' => $event]);

            $this->assertSame(1, substr_count(Artisan::output(), $method));
        }
    }
}
