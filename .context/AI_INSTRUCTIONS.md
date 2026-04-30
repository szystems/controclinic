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

---

## 🎯 Estrategia de Selección de Agente / Modelo (Optimización de Tokens)

> **Objetivo:** maximizar el uso del plan mensual de GitHub Copilot eligiendo el modelo más eficiente
> según la complejidad real de cada tarea. El usuario es responsable de cambiar manualmente el modelo
> en VS Code (Copilot Chat → selector de modelo) antes de iniciar la conversación. Si el agente detecta
> que la tarea actual es claramente de otro nivel, **debe sugerir explícitamente** cambiar de modelo
> antes de continuar.

### Modelos disponibles y costo (multiplicador de créditos por solicitud)

| Tier | Modelo | Multiplicador | Uso recomendado |
|------|--------|---------------|-----------------|
| **Premium principal** | Claude Opus 4.7 | x7.5 | Planificación arquitectónica, refactors complejos, decisiones de diseño multi-archivo, debugging profundo, seguridad. |
| **Premium alternativo** | GPT-5.5 | x7.5 | Igual que Opus si Opus no está disponible o se quiere segunda opinión. |
| **Equilibrado (preferido por defecto)** | Claude Sonnet 4.6 | x1 | Implementación de features estándar, edición de código siguiendo patrones existentes, escribir tests, revisión de PR. **DEBE SER EL DEFAULT.** |
| **Económico / fallback** | GPT-4.1 mini, Gemini Flash u otros x0.25–x0.33 | <x1 | Tareas triviales: renombrar variables, formatear código, traducir strings i18n, fixes de typos, regenerar boilerplate, responder preguntas factuales del proyecto. |

> ⚠️ El usuario indicó que **Claude Sonnet 4.6 (x1) es el modelo de confianza secundario** y los modelos
> económicos (<x1) son aceptables solo para tareas triviales o como respaldo si se acaban los créditos premium.

### Reglas de decisión (qué modelo pedir según la tarea)

**Usar Sonnet 4.6 (x1) — DEFAULT:**
- Implementar un componente Livewire siguiendo el patrón existente.
- Agregar un campo a un modelo + migración + validación.
- Escribir/ajustar tests Feature o Unit.
- Editar vistas Blade conocidas.
- Traducciones `lang/{es,en}/*.php`.
- Responder dudas sobre el código existente.
- Refactors localizados a un solo archivo o método.

**Subir a Opus 4.7 / GPT-5.5 (x7.5) — solo cuando:**
- Diseñar un módulo nuevo desde cero (ej. Facturación, Configuración General).
- Refactor multi-archivo con riesgo de romper tests.
- Decisiones de arquitectura (multi-tenancy, permisos, performance).
- Debugging de bugs no obvios o intermitentes.
- Auditoría de seguridad (OWASP, autorización, multi-tenant leaks).
- Migraciones de datos delicadas en producción.
- Cuando Sonnet ya falló dos veces seguidas en el mismo problema.

**Bajar a económico (<x1) — usar cuando:**
- Renombrar símbolos, formatear, ordenar imports.
- Aplicar Pint / linter automático.
- Buscar archivos o leer código sin razonamiento.
- Generar texto repetitivo (i18n, fixtures, factories triviales).
- El usuario lo solicita explícitamente para ahorrar créditos.

### Protocolo del agente

1. **Al inicio de cada conversación**, evaluar la complejidad de la solicitud antes de actuar.
2. Si el modelo activo **NO es el ideal** para la tarea:
   - Si está sobre-dimensionado (tarea trivial en Opus): sugerir bajar a Sonnet o económico.
   - Si está sub-dimensionado (tarea compleja en Sonnet/económico tras intentos fallidos): sugerir subir a Opus/GPT-5.5.
   - Formato sugerido: *"Esta tarea es [trivial/estándar/compleja]. Para optimizar créditos, te recomiendo cambiar a `<modelo>` antes de continuar. ¿Procedo igual o prefieres cambiar?"*
3. **No cambiar de modelo a mitad de tarea** sin confirmar — interrumpe el contexto.
4. **Compactar contexto** cuando una conversación supere ~50 mensajes: resumir lo hecho y proponer reiniciar.
5. **Reutilizar memoria del proyecto** (`.context/`) en lugar de re-explorar el codebase repetidamente.
6. **Preferir tools especializados** (search_subagent, grep_search, file_search) sobre semantic_search ya que consumen menos contexto.
7. **Evitar lecturas redundantes** del mismo archivo en una sesión.
8. **Acciones paralelas** cuando sean independientes (multi_replace_string_in_file en lugar de varias llamadas).

### Señales de tarea trivial (candidata a económico)

- Cambio de < 10 líneas en un solo archivo.
- Sin lógica condicional nueva.
- Sin tests nuevos requeridos.
- Sin impacto en multi-tenant ni permisos.
- Strings, formato, imports, comentarios, traducciones.

### Señales de tarea compleja (candidata a Opus/GPT-5.5)

- Múltiples archivos / migraciones simultáneas.
- Nuevas tablas o cambios en relaciones.
- Diseño de API o flujos de UX nuevos.
- Performance / índices / queries N+1.
- Tests de integración multi-componente.
- Bugs de concurrencia, transacciones, caché.

### Fallback si se agotan créditos premium

Orden sugerido si Opus/GPT-5.5 ya no están disponibles:
1. **Claude Sonnet 4.6 (x1)** — primer fallback confiable.
2. **GPT-4.1 / Gemini 2.5 Pro (x0.33)** — solo para tareas que ya estén bien planificadas y solo requieran ejecución.
3. **Modelos económicos (<x1)** — solo lectura, búsqueda, formateo. Evitar para lógica de negocio crítica.

### Ahorro adicional de tokens en cualquier modelo

- Respuestas cortas, sin introducciones ni resúmenes innecesarios.
- No re-leer archivos ya leídos en la sesión.
- No incluir bloques de código completos cuando un diff o referencia es suficiente.
- No pegar logs largos: filtrar con grep / head antes de mostrar.
- Usar enlaces a líneas (`[archivo.php](archivo.php#L10-L20)`) en lugar de copiar el código.
- Cerrar la conversación cuando la tarea esté completa; no extenderla con charlas.
