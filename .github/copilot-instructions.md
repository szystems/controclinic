# GitHub Copilot Instructions for ControClinic

## Project Overview

ControClinic is a multi-tenant SaaS platform for medical clinics built with Laravel 12 and the TALL Stack (Tailwind, Alpine.js, Laravel, Livewire).

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3+
- **Frontend:** Livewire 3, Alpine.js, Tailwind CSS
- **Database:** SQLite (dev) / MySQL 8.0 (prod)
- **Payments:** Paddle (Laravel Cashier)
- **Auth:** Laravel Breeze with Livewire

## Key Packages

- `livewire/livewire` - Reactive UI components
- `spatie/laravel-permission` - Roles and permissions
- `spatie/laravel-activitylog` - Audit logging
- `laravel/cashier-paddle` - Subscription billing
- `mcamara/laravel-localization` - Multi-language (ES/EN)

## Architecture Patterns

### Multi-tenancy
- Single database with `clinic_id` isolation
- Use `BelongsToClinic` trait on tenant-scoped models
- TenantMiddleware resolves clinic from URL
- Access current clinic via `app('current_clinic')`

### Models
- Main tenant: `Clinic` (UUID)
- `User` belongs to Clinic (bigint ID)
- `Patient`, `Appointment`, `MedicalRecord` (UUID)
- Always include `clinic_id` in fillable

### Roles (6 total)
- owner, doctor, assistant, secretary, receptionist, admin
- Check permissions with `$user->can('permission.name')`
- Use `@can` directive in Blade

## Code Conventions

### Naming
- Models: PascalCase singular (`Patient.php`)
- Livewire: `App\Livewire\App\{Resource}\{Action}` 
- Routes: `area.resource.action` (e.g., `app.patients.index`)
- Views: kebab-case (`patient-card.blade.php`)

### Livewire Components
```php
namespace App\Livewire\App\Patients;

class Index extends Component
{
    use WithPagination;
    
    public string $search = '';
    
    // Use computed properties for queries
    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->paginate(15);
    }
    
    public function render()
    {
        return view('livewire.app.patients.index', [
            'patients' => $this->patients,
        ]);
    }
}
```

### Blade Views
- Use `__('file.key')` for translations
- Use x-components when possible
- Use Alpine.js for simple interactivity
- Follow Tailwind CSS class ordering

### Routes
```php
Route::prefix('app/{clinic}')
    ->middleware(['auth', 'verified', TenantMiddleware::class])
    ->group(function () {
        Route::resource('patients', PatientController::class);
    });
```

## Important Files

- `.context/` - Full project documentation
- `.context/STATUS.md` - Current progress
- `.context/TASKS.md` - Pending tasks
- `.context/CONVENTIONS.md` - Code standards
- `.context/MODELS.md` - Model documentation

## Test Data

```
Clinic: demo (slug)
Doctor: doctor@controclinic.com / password
Assistant: asistente@controclinic.com / password
```

## Commands

```bash
php artisan serve              # Start server
npm run dev                    # Watch assets
php artisan migrate:fresh --seed  # Reset DB
./vendor/bin/pint              # Format code
```

## Do NOT

- Create queries without tenant scope
- Hardcode text (use translations)
- Use sequential IDs in public URLs
- Expose medical data without permission checks
- Commit credentials or API keys

## Do

- Verify permissions before actions
- Use transactions for multiple operations
- Validate with Form Requests
- Log important actions
- Handle errors gracefully
- Update `.context/STATUS.md` after completing features
