<?php

namespace App\Providers;

use App\Listeners\PaddleEventListener;
use App\Models\Clinic;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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

        // ==================== RATE LIMITERS ====================
        // Limita endpoints sensibles globalmente. Aplicar con middleware('throttle:<name>').
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Endpoints sensibles (registro, cambios de email/password, transferencias).
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Webhooks de Paddle: protección frente a abuso (la firma se valida en Cashier).
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
