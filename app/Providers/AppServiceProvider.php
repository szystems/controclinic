<?php

namespace App\Providers;

use App\Listeners\PaddleEventListener;
use App\Models\Clinic;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route model binding: resolver {clinic} por slug, o por public_portal_slug si no se encuentra
        Route::bind('clinic', function (string $value) {
            return Clinic::where('slug', $value)
                ->orWhere('public_portal_slug', $value)
                ->firstOrFail();
        });

        // Paddle webhook event listeners
        Event::subscribe(PaddleEventListener::class);
    }
}
