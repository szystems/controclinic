<?php

use App\Http\Controllers\AppointmentConfirmationController;
use App\Http\Middleware\CheckPlanLimits;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Http\Middleware\TenantMiddleware;
use App\Livewire\Actions\Logout;
use App\Livewire\Admin\Clinics\Show;
use App\Livewire\Admin\Plans\Edit;
use App\Livewire\App\Appointments\Calendar as AppointmentsCalendar;
use App\Livewire\App\Appointments\Create as AppointmentsCreate;
use App\Livewire\App\Appointments\Edit as AppointmentsEdit;
use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Appointments\Show as AppointmentsShow;
use App\Livewire\App\AuditLog\Index as AuditLogIndex;
use App\Livewire\App\Billing\Index as BillingIndex;
use App\Livewire\App\Dashboard;
use App\Livewire\App\Invitations\Accept as InvitationAccept;
use App\Livewire\App\MedicalRecords\Create as MedicalRecordsCreate;
use App\Livewire\App\MedicalRecords\Edit as MedicalRecordsEdit;
use App\Livewire\App\MedicalRecords\Index as MedicalRecordsIndex;
use App\Livewire\App\MedicalRecords\Show as MedicalRecordsShow;
use App\Livewire\App\Onboarding\Index;
use App\Livewire\App\Patients\Create as PatientsCreate;
use App\Livewire\App\Patients\Edit as PatientsEdit;
use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Patients\Show as PatientsShow;
use App\Livewire\App\Reports\Index as ReportsIndex;
use App\Livewire\App\Schedule\Index as ScheduleIndex;
use App\Livewire\App\Settings\Index as SettingsIndex;
use App\Livewire\App\Staff\Create as StaffCreate;
use App\Livewire\App\Staff\Edit as StaffEdit;
use App\Livewire\App\Staff\Index as StaffIndex;
use App\Livewire\Public\Booking;
use App\Models\Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    $checks = [];
    $status = 200;

    // Database
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (Throwable $e) {
        $checks['database'] = 'error';
        $status = 503;
    }

    // Cache
    try {
        Cache::put('health_check', true, 5);
        Cache::forget('health_check');
        $checks['cache'] = 'ok';
    } catch (Throwable $e) {
        $checks['cache'] = 'error';
        $status = 503;
    }

    // Storage
    try {
        Storage::disk('local')->put('.health', now()->toIso8601String());
        Storage::disk('local')->delete('.health');
        $checks['storage'] = 'ok';
    } catch (Throwable $e) {
        $checks['storage'] = 'error';
        $status = 503;
    }

    $checks['app'] = config('app.name');
    $checks['env'] = config('app.env');
    $checks['status'] = $status === 200 ? 'healthy' : 'degraded';

    return response()->json($checks, $status);
})->name('health');

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
| Appointment Confirmation / Cancellation (public, no auth required)
|--------------------------------------------------------------------------
*/
Route::get('/appointment/confirm/{token}', [AppointmentConfirmationController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('appointment.confirm');

Route::get('/appointment/cancel/{token}', [AppointmentConfirmationController::class, 'cancel'])
    ->middleware('throttle:10,1')
    ->name('appointment.cancel');

Route::post('logout', function () {
    (new Logout)();

    return redirect('/');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Auth Routes (Login/Register handled by Breeze)
|--------------------------------------------------------------------------
*/
Route::get('dashboard', function () {
    $user = auth()->user();

    // Super admins van directo al panel de administración de ControClinic
    if ($user->is_super_admin) {
        return redirect()->route('admin.dashboard');
    }

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

                // Medical Records (nested under patients)
                Route::prefix('patients/{patient}/records')->name('records.')->group(function () {
                    Route::get('/', MedicalRecordsIndex::class)->name('index');

                    // Write routes (require canWrite) — must come BEFORE /{record} catch-all
                    Route::middleware('can.write')->group(function () {
                        Route::get('/create', MedicalRecordsCreate::class)->name('create');
                        Route::get('/{record}/edit', MedicalRecordsEdit::class)->name('edit');
                    });

                    Route::get('/{record}', MedicalRecordsShow::class)->name('show');
                });

                // Appointments
                Route::prefix('appointments')->name('appointments.')->group(function () {
                    Route::get('/', AppointmentsIndex::class)->name('index');
                    Route::get('/calendar', AppointmentsCalendar::class)->name('calendar');

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
                // Reportes
                Route::get('/reports', ReportsIndex::class)->name('reports');
                // Registro de auditoría (owner y admin)
                Route::middleware('can:audit.view')->get('/audit-log', AuditLogIndex::class)->name('audit-log');
                // Bloqueo de horarios
                Route::middleware('can:schedule.manage')->get('/schedule', ScheduleIndex::class)->name('schedule');
                // Perfil de usuario (tenantizado)
                Route::get('/profile', App\Livewire\App\Profile\Index::class)->name('profile');

                // Facturación (invoices)
                Route::middleware('can:invoices.view')->prefix('invoices')->name('invoices.')->group(function () {
                    Route::get('/', App\Livewire\App\Invoices\Index::class)->name('index');
                    Route::middleware('can:invoices.create')->get('/create', App\Livewire\App\Invoices\Create::class)->name('create');
                    Route::get('/{invoice}', App\Livewire\App\Invoices\Show::class)->name('show');
                    Route::middleware('can:invoices.print')->get('/{invoice}/pdf', function (\App\Models\Clinic $clinic, \App\Models\Invoice $invoice) {
                        abort_unless($invoice->clinic_id === $clinic->id, 404);
                        $invoice->loadMissing(['patient', 'doctor', 'items', 'payments']);
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('invoice', 'clinic'));
                        return $pdf->stream("factura-{$invoice->invoice_number}.pdf");
                    })->name('pdf');
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
        Route::get('/settings', App\Livewire\Admin\Settings\Index::class)->name('settings');
    });

require __DIR__.'/auth.php';
