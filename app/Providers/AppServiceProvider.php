<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TenantMiddleware;
use App\Listeners\PaddleEventListener;
use App\Models\Clinic;
use App\Models\User;
use App\Policies\SuperAdminPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Gate::policy(User::class, SuperAdminPolicy::class);

        // Route model binding: resolver {clinic} por slug, o por public_portal_slug si no se encuentra
        Route::bind('clinic', function (string $value) {
            return Clinic::where('slug', $value)
                ->orWhere('public_portal_slug', $value)
                ->firstOrFail();
        });

        // Paddle webhook event listeners
        Event::subscribe(PaddleEventListener::class);

        // Registrar último acceso al autenticarse
        Event::listen(Login::class, function (Login $event) {
            $event->user->updateLastLogin(request()->ip());
        });

        // Livewire persistent middleware: garantiza que TenantMiddleware y SetLocale
        // se re-ejecuten en cada POST /livewire/update, manteniendo `current_clinic`
        // bindeado y el locale correcto durante hidrataciones y acciones (exportPdf, etc.).
        Livewire::addPersistentMiddleware([
            TenantMiddleware::class,
            SetLocale::class,
        ]);

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
