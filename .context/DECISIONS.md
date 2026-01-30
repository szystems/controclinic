# 🎯 Decisiones de Arquitectura

> Registro de decisiones importantes del proyecto

---

## ADR-001: Multi-tenancy con Single Database

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos aislar datos entre clínicas (tenants) de manera segura.

### Opciones Consideradas
1. **Database per tenant** - Una BD por clínica
2. **Schema per tenant** - Un schema por clínica
3. **Single database con tenant_id** - Una BD con columna clinic_id

### Decisión
Usar **Single Database con clinic_id** en todas las tablas.

### Razones
- Simplicidad de implementación y mantenimiento
- Menor costo de infraestructura
- Fácil de migrar y hacer backups
- Suficiente para el volumen esperado (< 1000 clínicas)
- Laravel Global Scopes hacen el aislamiento automático

### Consecuencias
- ✅ Setup simple
- ✅ Bajo costo
- ✅ Fácil de escalar verticalmente
- ⚠️ Requiere cuidado con queries sin scope
- ⚠️ Límite teórico de escala (millones de registros)

---

## ADR-002: UUID para entidades principales

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos identificadores únicos para Clinic, Patient, Appointment, MedicalRecord.

### Opciones Consideradas
1. **Auto-increment bigint** - Simple, secuencial
2. **UUID v4** - Único global, no predecible
3. **ULID** - Ordenable, único

### Decisión
Usar **UUID v4** para entidades principales (excepto User que mantiene bigint).

### Razones
- No expone información secuencial
- Seguro para URLs públicas (portal pacientes)
- Compatible con importación/exportación
- Laravel HasUuids trait disponible

### Consecuencias
- ✅ URLs seguras
- ✅ No expone cantidad de registros
- ⚠️ Índices ligeramente más grandes
- ⚠️ No ordenables por creación (usar created_at)

---

## ADR-003: Livewire para UI interactiva

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos UI reactiva sin complejidad de SPA.

### Opciones Consideradas
1. **Blade tradicional** - Simple pero sin reactividad
2. **Livewire** - Reactivo, server-side
3. **Inertia + Vue/React** - SPA-like
4. **Full SPA** - React/Vue separado

### Decisión
Usar **Livewire 3** con Alpine.js (TALL Stack).

### Razones
- Mantiene código en PHP (un solo lenguaje)
- Integración nativa con Laravel
- Sin build step complejo
- Perfecto para apps CRUD
- Comunidad activa

### Consecuencias
- ✅ Desarrollo rápido
- ✅ Un solo stack tecnológico
- ✅ SEO friendly
- ⚠️ Más requests al servidor
- ⚠️ Menos adecuado para UIs muy complejas

---

## ADR-004: Paddle como procesador de pagos

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos cobrar suscripciones globalmente (LATAM + España + USA).

### Opciones Consideradas
1. **Stripe** - Popular pero complejo para LATAM
2. **Paddle** - Merchant of Record, maneja impuestos
3. **MercadoPago** - Solo LATAM
4. **PayPal** - Universal pero fees altos

### Decisión
Usar **Paddle** como Merchant of Record.

### Razones
- Maneja impuestos (IVA, sales tax) automáticamente
- Soporta múltiples monedas
- Es el "merchant of record" (nosotros no manejamos dinero)
- Laravel Cashier for Paddle disponible
- Perfecto para SaaS global

### Consecuencias
- ✅ Compliance de impuestos automático
- ✅ Un solo proveedor global
- ✅ Menos responsabilidad legal
- ⚠️ Fees más altos que Stripe (~5-8%)
- ⚠️ Menos control sobre checkout

---

## ADR-005: SQLite para desarrollo, MySQL para producción

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos base de datos para desarrollo local y producción.

### Decisión
- **Desarrollo:** SQLite (sin configuración)
- **Producción:** MySQL 8.0

### Razones
- SQLite: Zero config, perfecto para dev
- MySQL: Probado, escalable, JSON support

### Consecuencias
- ✅ Setup de dev instantáneo
- ⚠️ Algunas diferencias de sintaxis
- ⚠️ Testing debe considerar ambos

---

## ADR-006: Roles con Spatie Permission

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos sistema de roles y permisos flexible.

### Opciones Consideradas
1. **Enum simple en User** - Básico
2. **Spatie Permission** - Completo, probado
3. **Bouncer** - Similar a Spatie
4. **Custom** - Máximo control

### Decisión
Usar **Spatie Laravel Permission**.

### Razones
- Estándar de la industria
- Bien documentado
- Roles + Permisos granulares
- Middleware incluido
- Cache automático

### Consecuencias
- ✅ Flexible y potente
- ✅ Blade directives (@can)
- ⚠️ Tablas adicionales
- ⚠️ Curva de aprendizaje inicial

---

## ADR-007: Contexto AI con carpeta .context

**Fecha:** 2026-01-28  
**Estado:** Aceptada

### Contexto
Necesitamos que herramientas AI (Copilot, Claude, etc.) entiendan el proyecto.

### Decisión
Crear carpeta `.context/` con documentación estructurada.

### Archivos:
- PROJECT.md - Info general
- ARCHITECTURE.md - Estructura técnica
- STATUS.md - Estado actual
- CONVENTIONS.md - Estándares de código
- MODELS.md - Documentación de modelos
- ROADMAP.md - Plan de desarrollo
- TASKS.md - Tareas pendientes
- DECISIONS.md - Este archivo

### Razones
- Contexto persistente entre sesiones
- Documentación viva del proyecto
- Ayuda a onboarding de nuevos devs
- Mejora respuestas de AI assistants

---

## Template para nuevas decisiones

```markdown
## ADR-XXX: [Título]

**Fecha:** YYYY-MM-DD  
**Estado:** Propuesta | Aceptada | Rechazada | Deprecada

### Contexto
[Descripción del problema o necesidad]

### Opciones Consideradas
1. **Opción A** - Descripción
2. **Opción B** - Descripción

### Decisión
[Qué se decidió]

### Razones
- Razón 1
- Razón 2

### Consecuencias
- ✅ Positivo
- ⚠️ Trade-off
- ❌ Negativo
```
