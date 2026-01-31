<?php

use App\Http\Middleware\TenantMiddleware;
use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Patients\Create as PatientsCreate;
use App\Livewire\App\Patients\Edit as PatientsEdit;
use App\Livewire\App\Patients\Show as PatientsShow;
use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Appointments\Create as AppointmentsCreate;
use App\Livewire\App\Appointments\Edit as AppointmentsEdit;
use App\Livewire\App\Appointments\Show as AppointmentsShow;
use App\Livewire\App\Settings\Index as SettingsIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Language Switch Route
|--------------------------------------------------------------------------
*/
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'es'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

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

        // Appointments
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', AppointmentsIndex::class)->name('index');
            Route::get('/create', AppointmentsCreate::class)->name('create');
            Route::get('/calendar', AppointmentsIndex::class)->name('calendar'); // TODO: Calendar component
            Route::get('/{appointment}', AppointmentsShow::class)->name('show');
            Route::get('/{appointment}/edit', AppointmentsEdit::class)->name('edit');
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
