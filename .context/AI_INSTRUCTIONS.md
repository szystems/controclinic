# 🤖 Instrucciones para AI Assistants

> Este archivo contiene instrucciones para GitHub Copilot, Claude, y otros AI assistants que trabajen en este proyecto.

---

## Sobre el Proyecto

**ControClinic** es un SaaS multi-tenant para gestión de clínicas médicas. Está construido con Laravel 12 y el TALL Stack (Tailwind, Alpine.js, Laravel, Livewire).

## Contexto Importante

1. **Multi-tenancy:** Todos los datos están aislados por `clinic_id`. Usar el trait `BelongsToClinic` en modelos.

2. **Roles:** Hay 6 roles (owner, doctor, assistant, secretary, receptionist, admin). Verificar permisos antes de acciones.

3. **Idiomas:** El sistema es bilingüe (ES/EN). Usar `__('archivo.clave')` para traducciones.

4. **UUIDs:** Clinic, Patient, Appointment, MedicalRecord usan UUID. User usa bigint.

---

## Convenciones de Código

### Al crear modelos:
```php
// Usar estos traits según necesidad:
use HasUuids;           // Para UUID primary key
use SoftDeletes;        // Para soft delete
use BelongsToClinic;    // Para tenant isolation
use LogsActivity;       // Para audit log

// Siempre incluir clinic_id en fillable si aplica
protected $fillable = ['clinic_id', ...];
```

### Al crear componentes Livewire:
```php
// Ubicación: app/Livewire/App/{Resource}/{Action}.php
// Vista: resources/views/livewire/app/{resource}/{action}.blade.php

// Usar WithPagination para listas
// Usar computed properties para queries
// Emitir eventos para comunicación entre componentes
```

### Al crear vistas Blade:
```blade
{{-- Usar componentes x-* cuando sea posible --}}
{{-- Usar __() para traducciones --}}
{{-- Usar Tailwind CSS para estilos --}}
{{-- Usar Alpine.js para interactividad simple --}}
```

### Al crear rutas:
```php
// Siempre incluir middleware de tenant para rutas de clínica
Route::prefix('app/{clinic}')
    ->middleware(['auth', 'verified', TenantMiddleware::class])
    ->group(function () {
        // ...
    });
```

---

## Archivos de Referencia

Antes de hacer cambios, revisa estos archivos de contexto:

| Archivo | Contenido |
|---------|-----------|
| `.context/PROJECT.md` | Info general del proyecto |
| `.context/ARCHITECTURE.md` | Estructura y patrones |
| `.context/STATUS.md` | Estado actual y progreso |
| `.context/CONVENTIONS.md` | Estándares de código |
| `.context/MODELS.md` | Documentación de modelos |
| `.context/ROADMAP.md` | Plan de desarrollo |
| `.context/TASKS.md` | Tareas pendientes |
| `.context/DECISIONS.md` | Decisiones de arquitectura |

---

## Comandos Útiles

```bash
# Desarrollo
php artisan serve                    # Iniciar servidor
npm run dev                          # Compilar assets (watch)
npm run build                        # Build producción

# Base de datos
php artisan migrate                  # Ejecutar migraciones
php artisan migrate:fresh --seed     # Reset BD con seeders
php artisan db:seed                  # Solo seeders

# Livewire
php artisan make:livewire App/Patients/Index  # Crear componente

# Testing
php artisan test                     # Ejecutar tests
./vendor/bin/pint                    # Formatear código

# Caché
php artisan optimize:clear           # Limpiar todo el caché
```

---

## Datos de Prueba

```yaml
Clínica Demo:
  slug: demo
  URL: /app/demo

Usuario Doctor:
  email: doctor@controclinic.com
  password: password
  role: owner

Usuario Asistente:
  email: asistente@controclinic.com
  password: password
  role: assistant
```

---

## Qué NO hacer

❌ No crear queries sin considerar el tenant scope
❌ No hardcodear textos, usar traducciones
❌ No usar IDs secuenciales en URLs públicas
❌ No exponer datos médicos sin verificar permisos
❌ No modificar migraciones ya ejecutadas en producción
❌ No commitear credenciales o keys

---

## Qué SÍ hacer

✅ Verificar permisos antes de acciones
✅ Usar transacciones para operaciones múltiples
✅ Validar inputs con Form Requests
✅ Loguear acciones importantes (activity log)
✅ Manejar errores gracefully
✅ Escribir tests para lógica crítica
✅ Actualizar .context/STATUS.md al completar features

---

## Preguntas Frecuentes para AI

**P: ¿Cómo accedo a la clínica actual?**
R: `app('current_clinic')` o `$currentClinic` en vistas.

**P: ¿Cómo verifico si un usuario puede hacer algo?**
R: `auth()->user()->can('permission.name')` o `@can('permission.name')` en Blade.

**P: ¿Dónde pongo la lógica de negocio?**
R: En el modelo para lógica simple, en Services para lógica compleja.

**P: ¿Cómo manejo errores en Livewire?**
R: Usa `$this->addError()` para errores de validación, `session()->flash()` para mensajes.

**P: ¿Cómo actualizo el contexto después de cambios?**
R: Edita el archivo `.context/STATUS.md` para reflejar el nuevo estado.
