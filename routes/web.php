<?php

use App\Http\Middleware\TenantMiddleware;
use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Patients\Create as PatientsCreate;
use App\Livewire\App\Patients\Edit as PatientsEdit;
use App\Livewire\App\Patients\Show as PatientsShow;
use App\Livewire\App\Settings\Index as SettingsIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Marketing Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('public.home');
})->name('home');

Route::get('/pricing', function () {
    return view('public.pricing');
})->name('pricing');

Route::get('/contact', function () {
    return view('public.contact');
})->name('contact');

/*
|--------------------------------------------------------------------------
| Auth Routes (Login/Register handled by Breeze)
|--------------------------------------------------------------------------
*/
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Clinic App Routes
Route::prefix('app/{clinic}')
    ->middleware(['auth', 'verified', TenantMiddleware::class])
    ->name('app.')
    ->group(function () {
        // Dashboard
        Route::view('/', 'app.dashboard')->name('dashboard');

        // Patients
        Route::prefix('patients')->name('patients.')->group(function () {
            Route::get('/', PatientsIndex::class)->name('index');
            Route::get('/create', PatientsCreate::class)->name('create');
            Route::get('/{patient}', PatientsShow::class)->name('show');
            Route::get('/{patient}/edit', PatientsEdit::class)->name('edit');
        });

        // Appointments (placeholder)
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::view('/', 'app.appointments.index')->name('index');
            Route::view('/create', 'app.appointments.create')->name('create');
            Route::view('/calendar', 'app.appointments.calendar')->name('calendar');
        });

        // Settings
        Route::get('/settings', SettingsIndex::class)->name('settings');
    });

// Public Portal Routes
Route::prefix('public/{clinic}')
    ->name('public.')
    ->group(function () {
        Route::view('/', 'public.clinic')->name('clinic');
    });

require __DIR__.'/auth.php';
