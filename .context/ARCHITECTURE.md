# 🏗️ Arquitectura de ControClinic

> Actualizado: 2026-03-23

## Estructura de Directorios

```
controclinic/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controllers tradicionales
│   │   ├── Middleware/
│   │   │   └── TenantMiddleware.php    # Aislamiento multi-tenant
│   │   └── Requests/           # Form Requests
│   ├── Livewire/               # Componentes Livewire
│   │   ├── Actions/
│   │   │   └── Logout.php
│   │   ├── Forms/
│   │   │   └── LoginForm.php
│   │   └── App/                # Dashboard de clínica
│   │       ├── Patients/       # CRUD Pacientes ✅
│   │       │   ├── Index.php
│   │       │   ├── Create.php
│   │       │   ├── Edit.php
│   │       │   └── Show.php
│   │       ├── Appointments/   # Sistema de Citas ✅
│   │       │   ├── Index.php
│   │       │   ├── Create.php
│   │       │   ├── Edit.php
│   │       │   └── Show.php
│   │       └── Settings/       # Configuración ✅
│   │           └── Index.php
│   ├── Models/
│   │   ├── Clinic.php          # Tenant principal (UUID)
│   │   ├── User.php            # Usuarios con roles
│   │   ├── Patient.php         # Pacientes (UUID)
│   │   ├── Appointment.php     # Citas (UUID)
│   │   └── MedicalRecord.php   # Historiales
│   ├── Traits/
│   │   └── BelongsToClinic.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/             # 17 migraciones
│   ├── seeders/
│   │   └── DatabaseSeeder.php  # Datos demo
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── app/                # Vistas dashboard
│   │   │   ├── dashboard.blade.php
│   │   │   ├── patients/
│   │   │   └── appointments/
│   │   ├── public/             # Portal público
│   │   │   ├── home.blade.php
│   │   │   ├── pricing.blade.php
│   │   │   ├── contact.blade.php
│   │   │   └── clinic.blade.php
│   │   ├── layouts/
│   │   │   ├── app.blade.php       # Layout con colores dinámicos
│   │   │   ├── guest.blade.php     # Layout autenticación
│   │   │   └── public.blade.php    # Layout público
│   │   └── livewire/
│   │       ├── layout/
│   │       │   └── navigation.blade.php  # Nav con selector idioma
│   │       └── app/
│   │           ├── patients/   # 4 vistas Livewire
│   │           ├── appointments/ # 4 vistas Livewire
│   │           └── settings/   # Vista Settings (6 tabs)
│   ├── css/
│   │   └── app.css             # CSS con variables dinámicas
│   └── lang/
│       ├── es/                 # patients, settings, general, appointments
│       └── en/                 # patients, settings, general, appointments
├── routes/
│   ├── web.php                 # Rutas con {clinic} binding + lang.switch
│   └── auth.php                # Rutas de autenticación
├── docker/                     # 🔜 Configuración Docker
└── .context/                   # Documentación de contexto AI
```

## Modelos y Relaciones

```
Clinic (Tenant) - UUID
├── hasMany → User (doctors, staff, owner)
├── hasMany → Patient
├── hasMany → Appointment
├── hasMany → MedicalRecord
├── JSON → settings (configuraciones)
└── JSON → branding (logo, colores)

User - bigint
├── belongsTo → Clinic
├── hasMany → Patient (como primary_doctor)
├── hasMany → Appointment (como doctor)
└── hasMany → MedicalRecord (como doctor)

Patient - UUID
├── belongsTo → Clinic
├── belongsTo → User (primary_doctor)
├── hasMany → Appointment
└── hasMany → MedicalRecord

Appointment - UUID
├── belongsTo → Clinic
├── belongsTo → Patient
├── belongsTo → User (doctor)
└── belongsTo → User (created_by)

MedicalRecord - UUID
├── belongsTo → Clinic
├── belongsTo → Patient
├── belongsTo → User (doctor)
└── belongsTo → Appointment (opcional)
```

## Sistema de Roles (Spatie Permission)

```yaml
owner:
  - Propietario de la clínica
  - Gestión completa (usuarios, settings, billing)
  - Permisos: ALL

doctor:
  - Médico de la clínica
  - Ver/editar pacientes propios
  - Crear historiales médicos y citas
  - Permisos: patients.*, appointments.*, records.*

assistant:
  - Asistente médico
  - Apoyo al doctor
  - Permisos: patients.view/create/edit, appointments.*

receptionist:
  - Check-in de pacientes
  - Vista básica, gestión citas
  - Permisos: patients.view, appointments.view/edit
```

## Flujo Multi-tenant

```
Request /app/{clinic}/...
              ↓
         TenantMiddleware
              ↓
         Route Model Binding (slug → Clinic)
              ↓
         Verificar $user->clinic_id === $clinic->id
              ↓
         view()->share('clinic', $clinic)
              ↓
         Queries filtradas por clinic_id
              ↓
         Response (datos aislados)
```

## Sistema de Branding Dinámico

```
clinic.branding (JSON)
├── primary_color: #4F46E5
├── secondary_color: #EC4899
└── logo_path: (futuro)
         ↓
layouts/app.blade.php
├── PHP: hexToRgb(), darkenColor()
├── <style>:root { --color-primary: ... }
         ↓
resources/css/app.css
├── @layer utilities { .bg-primary {} }
├── Override: .bg-indigo-600 → var(--color-primary)
         ↓
Tailwind classes usan colores de la clínica
```

## Patrones de Código

### Modelos con UUID
```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Patient extends Model
{
    use HasUuids, SoftDeletes;
    
    protected $casts = [
        'birthdate' => 'date',
        'emergency_contact' => 'array',
    ];
}
```

### Componentes Livewire con Clinic
```php
// Convención: App\Livewire\{Area}\{Recurso}\{Acción}
// Ejemplo: App\Livewire\App\Patients\Index
// Ejemplo: App\Livewire\App\Patients\Create

class Create extends Component
{
    public Clinic $clinic;
    
    public function mount(Clinic $clinic)
    {
        $this->clinic = $clinic;
    }
}
```

### Rutas con Tenant
```php
Route::prefix('app/{clinic}')
    ->middleware(['auth', 'verified', TenantMiddleware::class])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/patients', Patients\Index::class)->name('patients.index');
        Route::get('/settings', Settings\Index::class)->name('settings.index');
    });
```

## Configuración Regional

### Zonas Horarias Soportadas (36)
- **Canadá**: 6 zonas (Pacific, Mountain, Central, Eastern, Atlantic, Newfoundland)
- **Estados Unidos**: 7 zonas (Pacific, Mountain, Central, Eastern, Alaska, Hawaii, Arizona)
- **México**: 4 zonas (Ciudad de México, Cancún, Chihuahua, Tijuana)
- **Centroamérica**: 6 zonas (Guatemala, Honduras, El Salvador, Nicaragua, Costa Rica, Panamá)
- **Caribe**: 3 zonas (Santo Domingo, Puerto Rico, Jamaica)
- **Sudamérica**: 10 zonas (Argentina, Chile, Colombia, Perú, Ecuador, Venezuela, Bolivia, Paraguay, Uruguay, Brasil)
- **Europa**: 6 zonas (Madrid, Londres, París, Berlín, Roma, Lisboa)

### Monedas Soportadas (22)
USD, CAD, MXN, GTQ, HNL, NIO, CRC, PAB, DOP, COP, PEN, CLP, ARS, VES, BOB, PYG, UYU, BRL, EUR, GBP, CHF

## Convenciones de Desarrollo

1. **Rutas**: Siempre incluir `{clinic}` en rutas de dashboard
2. **Queries**: Siempre filtrar por `clinic_id`
3. **Activity Log**: Usar para auditoría (subject_id soporta UUID)
4. **Traducciones**: Archivos en `lang/{locale}/{modulo}.php`
5. **Componentes**: Un componente Livewire por acción (Create, Edit, Show, Index)
