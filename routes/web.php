<?php

use App\Http\Middleware\CheckPlanLimits;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Http\Middleware\TenantMiddleware;
use App\Livewire\Admin\Clinics\Show;
use App\Livewire\Admin\Plans\Edit;
use App\Livewire\App\Appointments\Create as AppointmentsCreate;
use App\Livewire\App\Appointments\Edit as AppointmentsEdit;
use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Appointments\Show as AppointmentsShow;
use App\Livewire\App\Billing\Index as BillingIndex;
use App\Livewire\App\Dashboard;
use App\Livewire\App\Invitations\Accept as InvitationAccept;
use App\Livewire\App\Onboarding\Index;
use App\Livewire\App\Patients\Create as PatientsCreate;
use App\Livewire\App\Patients\Edit as PatientsEdit;
use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Patients\Show as PatientsShow;
use App\Livewire\App\Settings\Index as SettingsIndex;
use App\Livewire\App\Staff\Create as StaffCreate;
use App\Livewire\App\Staff\Edit as StaffEdit;
use App\Livewire\App\Staff\Index as StaffIndex;
use App\Livewire\Public\Booking;
use App\Models\Plan;
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

        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
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
    return view('public.pricing', [
        'plans' => Plan::active()->ordered()->get(),
    ]);
})->name('pricing');

Route::get('/contact', function () {
    return view('public.contact');
})->name('contact');

Route::get('/terms', function () {
    return view('public.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('public.privacy');
})->name('privacy');

/*
|--------------------------------------------------------------------------
| Invitation Acceptance (public, no auth required)
|--------------------------------------------------------------------------
*/
Route::get('/invitation/{token}', InvitationAccept::class)
    ->middleware('throttle:10,1')
    ->name('invitations.accept');

/*
|--------------------------------------------------------------------------
| Auth Routes (Login/Register handled by Breeze)
|--------------------------------------------------------------------------
*/
Route::get('dashboard', function () {
    $user = auth()->user();
    $clinic = $user->clinic;

    // User has no clinic — shouldn't happen after new registration, but handle gracefully
    if (! $clinic) {
        return redirect()->route('register');
    }

    // Redirect to clinic dashboard (onboarding middleware will intercept if needed)
    return redirect()->route('app.dashboard', $clinic->slug);
})
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
        // Onboarding (accessible before onboarding is completed)
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', Index::class)->name('index');
        });

        // All other routes require onboarding to be completed
        Route::middleware([EnsureOnboardingCompleted::class])->group(function () {
            // Billing (accessible even with suspended plan)
            Route::prefix('billing')->name('billing.')->group(function () {
                Route::get('/', BillingIndex::class)->name('index');
            });

            // Routes that require active plan
            Route::middleware([CheckPlanLimits::class])->group(function () {
                // Dashboard
                Route::get('/', Dashboard::class)->name('dashboard');

                // Patients
                Route::prefix('patients')->name('patients.')->group(function () {
                    Route::get('/', PatientsIndex::class)->name('index');

                    // Write routes (require canWrite) — must come BEFORE /{patient} catch-all
                    Route::middleware('can.write')->group(function () {
                        Route::get('/create', PatientsCreate::class)->name('create');
                        Route::get('/{patient}/edit', PatientsEdit::class)->name('edit');
                    });

                    Route::get('/{patient}', PatientsShow::class)->name('show');
                });

                // Appointments
                Route::prefix('appointments')->name('appointments.')->group(function () {
                    Route::get('/', AppointmentsIndex::class)->name('index');
                    Route::get('/calendar', AppointmentsIndex::class)->name('calendar'); // TODO: Calendar component

                    // Write routes (require canWrite) — must come BEFORE /{appointment} catch-all
                    Route::middleware('can.write')->group(function () {
                        Route::get('/create', AppointmentsCreate::class)->name('create');
                        Route::get('/{appointment}/edit', AppointmentsEdit::class)->name('edit');
                    });

                    Route::get('/{appointment}', AppointmentsShow::class)->name('show');
                });

                // Settings (write — only fully-active clinics can change settings)
                Route::middleware('can.write')->get('/settings', SettingsIndex::class)->name('settings');

                // Staff
                Route::prefix('staff')->name('staff.')->group(function () {
                    Route::get('/', StaffIndex::class)->name('index');

                    // Write routes (require canWrite)
                    Route::middleware('can.write')->group(function () {
                        Route::get('/create', StaffCreate::class)->name('create');
                        Route::get('/{user}/edit', StaffEdit::class)->name('edit');
                    });
                });
            });
        });
    });

// Public Clinic Portal (booking) — resolves clinic by slug or public_portal_slug
Route::get('/c/{clinic}', Booking::class)
    ->name('public.clinic')
    ->where('clinic', '[A-Za-z0-9-]+');

// Legacy alias kept for backward compatibility
Route::get('/public/{clinic}', Booking::class)
    ->where('clinic', '[A-Za-z0-9-]+');

// Admin Panel Routes
Route::prefix('admin')
    ->middleware(['auth', 'verified', EnsureIsAdmin::class])
    ->name('admin.')
    ->group(function () {
        Route::get('/', App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/plans', App\Livewire\Admin\Plans\Index::class)->name('plans.index');
        Route::get('/plans/{plan}/edit', Edit::class)->name('plans.edit');
        Route::get('/clinics', App\Livewire\Admin\Clinics\Index::class)->name('clinics.index');
        Route::get('/clinics/{clinic}', Show::class)->name('clinics.show');
    });

require __DIR__.'/auth.php';
