<?php

namespace App\Providers;

use App\Models\Clinic;
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
        // Route model binding: resolver {clinic} por slug en lugar de id
        Route::bind('clinic', function (string $value) {
            return Clinic::where('slug', $value)->firstOrFail();
        });
    }
}
