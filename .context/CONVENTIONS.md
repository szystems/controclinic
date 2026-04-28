# 📐 Convenciones de Código

## Nomenclatura

### Archivos y Clases

```yaml
Models:
  Ubicación: app/Models/
  Naming: PascalCase singular
  Ejemplo: Patient.php, MedicalRecord.php

Controllers:
  Ubicación: app/Http/Controllers/
  Naming: PascalCase + Controller
  Ejemplo: PatientController.php

Livewire Components:
  Ubicación: app/Livewire/{Area}/{Resource}/
  Naming: PascalCase
  Ejemplo: app/Livewire/App/Patients/Index.php
  Vista: resources/views/livewire/app/patients/index.blade.php

Middleware:
  Ubicación: app/Http/Middleware/
  Naming: PascalCase
  Ejemplo: TenantMiddleware.php

Migrations:
  Naming: YYYY_MM_DD_HHMMSS_action_table_name.php
  Ejemplo: 2026_01_29_000001_create_clinics_table.php

Seeders:
  Ubicación: database/seeders/
  Naming: PascalCase + Seeder
  Ejemplo: DemoClinicSeeder.php
```

### Variables y Métodos

```php
// Variables: camelCase
$patientCount = 10;
$currentClinic = app('current_clinic');

// Métodos: camelCase
public function getFullNameAttribute(): string
public function canViewMedicalRecords(): bool

// Constantes: UPPER_SNAKE_CASE
public const STATUS_SCHEDULED = 'scheduled';
public const ROLE_DOCTOR = 'doctor';

// Propiedades de modelo: snake_case (base de datos)
protected $fillable = ['first_name', 'last_name', 'clinic_id'];
```

### Rutas

```php
// Naming: area.resource.action
Route::name('app.')->group(function () {
    Route::name('patients.')->group(function () {
        Route::get('/', ...)->name('index');      // app.patients.index
        Route::get('/create', ...)->name('create'); // app.patients.create
        Route::get('/{patient}', ...)->name('show'); // app.patients.show
    });
});
```

### Vistas

```yaml
Ubicación por área:
  - resources/views/app/       # Dashboard clínica
  - resources/views/admin/     # Admin SaaS
  - resources/views/public/    # Portal público
  - resources/views/livewire/  # Componentes Livewire
  - resources/views/components/ # Blade components

Naming: kebab-case.blade.php
Ejemplo: patient-card.blade.php
```

### Traducciones

```yaml
Archivos: lang/{locale}/{domain}.php  (locale: es | en)
Dominios actuales:
  - admin.php          # Panel super-admin SaaS
  - appointments.php   # Citas
  - appointments_mail.php # Emails de citas
  - auth.php           # Autenticación + Breeze
  - billing.php        # Suscripción Paddle
  - booking.php        # Portal público
  - features.php       # Features de planes
  - general.php        # Términos comunes (botones, navegación)
  - invitations.php    # Invitaciones de staff
  - onboarding.php     # Wizard de onboarding
  - patients.php       # Pacientes
  - settings.php       # Configuración de clínica
  - staff.php          # Equipo

Uso: __('domain.key') o @lang('domain.key')
Ejemplo: __('patients.new_patient')

Reglas:
  - Nunca hardcodear strings visibles al usuario.
  - Mantener paridad ES ↔ EN: si agregas key en es/, hacerlo en en/.
  - Validation messages: usar archivos validation.php por defecto de Laravel.
```

---

## Patrones de Código

### Modelos Eloquent

```php
<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use HasUuids, SoftDeletes, BelongsToClinic, LogsActivity;

    // 1. Propiedades de tabla
    protected $keyType = 'string';
    public $incrementing = false;

    // 2. Fillable/Guarded
    protected $fillable = [...];

    // 3. Casts
    protected $casts = [...];

    // 4. Constantes
    public const STATUS_ACTIVE = 'active';

    // 5. Relaciones
    public function clinic(): BelongsTo { ... }
    public function appointments(): HasMany { ... }

    // 6. Accessors/Mutators
    public function getFullNameAttribute(): string { ... }

    // 7. Scopes
    public function scopeActive($query) { ... }

    // 8. Métodos de negocio
    public function canBeDeleted(): bool { ... }
}
```

### Componentes Livewire

```php
<?php

namespace App\Livewire\App\Patients;

use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // 1. Propiedades públicas (state)
    public string $search = '';
    public string $status = '';

    // 2. Query string
    protected $queryString = ['search', 'status'];

    // 3. Listeners
    protected $listeners = ['patientCreated' => '$refresh'];

    // 4. Lifecycle hooks
    public function mount(): void { ... }
    public function updated($property): void { ... }

    // 5. Computed properties
    public function getPatientsProperty()
    {
        return Patient::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->status, fn($q) => $q->where('is_active', $this->status === 'active'))
            ->paginate(15);
    }

    // 6. Actions
    public function deletePatient(string $id): void { ... }

    // 7. Render
    public function render()
    {
        return view('livewire.app.patients.index', [
            'patients' => $this->patients,
        ]);
    }
}
```

### Form Requests

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('patients.create');
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('validation.required', ['attribute' => __('patients.first_name')]),
        ];
    }
}
```

---

## Estilo de Código

### PHP

```yaml
PSR-12: Seguir estándar PSR-12
Laravel Pint: Usar para formateo automático
Comando: ./vendor/bin/pint

Reglas adicionales:
  - Declarar tipos de retorno siempre
  - Usar named arguments cuando mejore legibilidad
  - Preferir early returns
  - Máximo 120 caracteres por línea
```

### Blade

```yaml
Indentación: 4 espacios
Directivas: Usar sintaxis corta (@if, @foreach)
Componentes: Preferir x-component sobre @include
Alpine.js: Usar x-data, x-show, x-on inline

Ejemplo:
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>
        Content
    </div>
</div>
```

### CSS (Tailwind)

```yaml
Orden de clases: 
  1. Layout (flex, grid, block)
  2. Spacing (p-*, m-*)
  3. Sizing (w-*, h-*)
  4. Typography (text-*, font-*)
  5. Colors (bg-*, text-*)
  6. Effects (shadow-*, rounded-*)
  7. States (hover:*, focus:*)

Ejemplo:
class="flex items-center justify-between p-4 w-full text-sm font-medium text-gray-900 bg-white rounded-lg shadow hover:bg-gray-50"
```

---

## Git

### Commits

```yaml
Formato: tipo(scope): descripción

Tipos:
  - feat: Nueva funcionalidad
  - fix: Corrección de bug
  - docs: Documentación
  - style: Formateo (no afecta lógica)
  - refactor: Refactorización
  - test: Tests
  - chore: Mantenimiento

Ejemplos:
  feat(patients): add patient search functionality
  fix(appointments): resolve timezone issue in calendar
  docs(readme): update installation instructions
```

### Branches

```yaml
Formato: tipo/descripción-corta

Ejemplos:
  feature/patient-crud
  fix/calendar-timezone
  hotfix/login-error
```

---

## Multi-tenancy y Activity Log

### Modelos tenant-scoped

Todo modelo que tenga `clinic_id` DEBE:

1. Usar el trait `App\Traits\BelongsToClinic` (aplica Global Scope automático).
2. Incluir `clinic_id` en `$fillable`.
3. Definir relación `clinic(): BelongsTo`.
4. Tener factory que setee `clinic_id` (no `null`).

```php
use App\Traits\BelongsToClinic;

class Patient extends Model
{
    use BelongsToClinic; // ← Filtra por app('current_clinic') automáticamente

    protected $fillable = ['clinic_id', /* ... */];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
```

> ⚠️ Para escapar el scope en jobs/comandos usa `Model::withoutGlobalScope('clinic')`.

### ⚠️ Defensa en profundidad: Route Model Binding

El Global Scope **NO basta** como única defensa cuando se usa Route Model Binding,
porque `SubstituteBindings` se ejecuta ANTES que `TenantMiddleware` (no hay
`current_clinic` bound aún → el scope no filtra). Por lo tanto, en cada
componente Livewire que reciba un modelo tenant-scoped via mount, validar:

```php
public function mount(Patient $patient): void
{
    abort_if($patient->clinic_id !== app('current_clinic')->id, 404);
    // ... resto de la lógica
}
```

Esto cubre el escenario de un usuario malicioso accediendo
`/app/{miClinica}/patients/{uuid_de_otra_clinica}`.

### Activity Log (Spatie)

Modelos sensibles (Patient, Appointment, MedicalRecord, User, Clinic) implementan `LogsActivity`:

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email', 'role'])  // Campos a registrar
        ->logOnlyDirty()                       // Solo cambios reales
        ->dontSubmitEmptyLogs();
}
```

Reglas:
- Nunca loguear `password`, tokens o datos sensibles directos.
- Para acciones custom (login, billing, invitaciones) usar `activity()->log(...)` explícito.

---

## Tests

```yaml
Stack: PHPUnit 11 + RefreshDatabase + Mail::fake() + Queue::fake()
Estructura:
  - tests/Feature/    # Tests de integración (HTTP + Livewire)
  - tests/Unit/       # Tests unitarios puros

Convenciones:
  - Un Test class por feature (PatientsCrudTest, BillingTest, etc.).
  - Usar factory states (Patient::factory()->forClinic($clinic)).
  - Para multi-tenant: crear 2 clínicas y verificar aislamiento.
  - Helpers en tests/TestCase.php (actingAsOwner, actingAsDoctor).

Comandos:
  php artisan test                          # Todos
  php artisan test --filter=PatientsTest    # Uno
  php artisan test --parallel               # Paralelos
```
