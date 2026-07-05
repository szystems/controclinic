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

## ADR-008: Política de Acceso de Cuenta (Read-Only)

**Fecha:** 2026-04-28
**Estado:** Aceptada (implementación pendiente — Fase 4)

### Contexto
Cuando expira el trial o un plan pagado caduca, el usuario actualmente recibe un 403 del `TenantMiddleware`, lo cual deja a la clínica sin poder ver sus datos ni renovar.

### Decisión
La cuenta NUNCA pierde acceso de lectura. Los estados se mapean a un `accessLevel`:

| Estado de cuenta             | Login | Lectura | Crear/Editar | Billing | Portal Público | Recordatorios |
|------------------------------|:-----:|:-------:|:------------:|:-------:|:--------------:|:-------------:|
| `active`                     | ✅    | ✅      | ✅           | ✅      | ✅             | ✅            |
| `trial` vigente              | ✅    | ✅      | ✅           | ✅      | ✅             | ✅            |
| `trial` expirado             | ✅    | ✅      | ❌           | ✅      | ❌             | ✅            |
| Plan pagado caducado         | ✅    | ✅      | ❌           | ✅      | ❌             | ✅            |
| `suspended` (acción admin)   | ✅    | ❌      | ❌           | ✅      | ❌             | ❌            |
| `cancelled`                  | ✅    | ❌      | ❌           | ✅      | ❌             | ❌            |
| Plan Free (cortesía)         | ✅    | ✅      | ✅           | ✅      | ✅             | ✅            |

### Implementación
- Método `Clinic::accessLevel()` devuelve enum: `full | read_only | billing_only`.
- Middleware `EnsureCanWrite` aplica en rutas de creación/edición.
- Componente `<x-account-status-banner>` muestra estado al usuario.
- Portal público chequea `accessLevel === full` antes de aceptar reservas.
- Recordatorios solo se envían si `accessLevel !== billing_only`.

### Razones
- Evita "secuestrar" datos del cliente.
- El cliente puede recuperar acceso completo pagando sin perder histórico.
- Soporte legal/regulatorio: las clínicas necesitan acceder a expedientes ante reclamos.

### Consecuencias
- ✅ Mejor UX y reputación.
- ⚠️ Costo de almacenamiento incluso de cuentas inactivas (mitigar con purga ≥ 12 meses cancelado).
- ❌ Más complejidad: cada acción de escritura debe pasar por el middleware.

---

## ADR-009: Notificaciones por Email vía Job Orquestador

**Fecha:** 2026-04-28
**Estado:** Aceptada (implementada)

### Contexto
Necesitamos enviar emails de citas (booking, confirmación, cancelación, recordatorio) sin acoplar la lógica al request HTTP.

### Decisión
Un único Job `SendAppointmentNotification` (`ShouldQueue`) recibe `(appointment, type)` y decide qué Mailable disparar. El locale se setea desde `clinic.locale` con `Mail::to()->locale($locale)`.

### Razones
- Un punto único de orquestación = más fácil de testear y observar.
- Locale por clínica respeta la configuración de cada tenant.
- Reintentos automáticos (3) por la cola si el SMTP falla.

### Consecuencias
- ✅ Desacoplamiento total del request.
- ✅ Tests con `Queue::fake()` y `Mail::fake()` triviales.
- ⚠️ Requiere worker `queue:work` en producción + scheduler para recordatorios.

---

## ADR-010: Plan Free como Cortesía (no autoservicio)

**Fecha:** 2026-04-28
**Estado:** ⚠️ Deprecada — sustituida por ADR-012 (freemium permanente)

### Contexto
Inicialmente el plan Free aparecía en `/pricing` y el onboarding como una opción gratis. Esto canibalizaba el plan Solo y atraía cuentas zombi.

### Decisión
- El plan Free **solo es asignable desde el panel admin** (`is_manual_plan = true`, con `manual_plan_reason`).
- En registro nuevo se asigna automáticamente `plan = 'solo'` con `status = 'trial'` y `trial_ends_at = now()->addDays(14)`.
- En `/pricing` y onboarding, el plan Free **no se ofrece** como opción.

### Razones
- Foco en conversión a planes pagos.
- Permite seguir regalando cuentas (clínicas asociadas, prensa, casos comerciales) sin contaminar el funnel.
- Alinea trial 14 días con la práctica del mercado SaaS.

### Consecuencias
- ✅ Funnel más limpio.
- ✅ Cortesías controladas.
- ⚠️ Hay que migrar clínicas Free preexistentes (mantenerlas con `is_manual_plan = true`).

> **NOTA (2026-06-21):** Esta decisión quedó **deprecada**. El registro actual ya crea
> clínicas en plan Free permanente (`plan_type='free'`, `is_manual_plan=true`, `status='active'`),
> NO en Solo con trial de 14 días. Ver **ADR-012** para el modelo vigente (freemium permanente).

---

## ADR-011: Modelo de marca — SZ Systems (entidad legal) / ControClinic (producto)

**Fecha:** 2026-06-21
**Estado:** Aceptada

### Contexto
La empresa está registrada legalmente en Canadá como **SZ Systems**. ControClinic es uno de
sus productos SaaS. El business number para activar Paddle pertenece a SZ Systems, no a
"ControClinic". Hay que definir cómo se relacionan ambas marcas en pagos, legales y UX, y
cómo dar confianza al comprador (que verá un cargo de SZ Systems por un producto ControClinic).

### Opciones Consideradas
1. **Crear todo a nombre de ControClinic** — requeriría registrar ControClinic como entidad; innecesario.
2. **Modelo casa-marca**: SZ Systems = entidad legal/merchant; ControClinic = marca de producto.
3. **Fusionar marcas** (solo SZ Systems) — diluye el producto, peor para marketing.

### Decisión
Adoptar el **modelo casa-marca** (opción 2):
- **Entidad legal / Merchant of Record (Paddle) / titular de contratos y dominio:** SZ Systems.
- **Marca de producto (app, dominio controclinic.com, marketing, UX):** ControClinic.
- En Paddle: cuenta a nombre de SZ Systems; **productos** nombrados `ControClinic — {Plan}`.
- En la app: footer/emails/legales muestran **"ControClinic by SZ Systems"** (Victoria, BC, Canada).
- Privacy/Terms nombran a **SZ Systems** como responsable del tratamiento de datos.
- szystems.com da espacio destacado a ControClinic para reforzar la cadena de confianza.

### Razones
- Es el patrón estándar de SaaS (entidad legal cobra, producto tiene su marca).
- No hay impedimento legal: Paddle como Merchant of Record cobra a nombre de SZ Systems.
- El usuario reconoce el cargo si el recibo y la app explicitan "ControClinic by SZ Systems".

### Consecuencias
- ✅ Cobro legalmente correcto sin registrar una segunda entidad.
- ✅ Marca de producto fuerte e independiente.
- ⚠️ Hay que mostrar explícitamente la relación SZ Systems ↔ ControClinic para evitar disputas/chargebacks por "cargo no reconocido".

---

## ADR-012: Freemium permanente (modelo de adquisición)

**Fecha:** 2026-06-21
**Estado:** Aceptada (sustituye a ADR-010)

### Contexto
ADR-010 definía: registro → plan Solo con trial de 14 días, Free solo por admin. Pero el
código de registro vigente crea clínicas en **Free permanente** (`is_manual_plan=true`,
acceso completo dentro de límites). Además el sitio público todavía promete "14 días de prueba
gratis", lo que **contradice** el comportamiento real y confunde al usuario.

### Opciones Consideradas
1. **Trial 14 días al registro** (ADR-010 original) — presión temporal, fricción, abandono al día 15.
2. **Freemium permanente** — registro gratis sin límite de tiempo; se sube de plan al topar límites o querer features.
3. **Híbrido** — Free permanente + trial de plan pago al hacer upgrade.

### Decisión
Adoptar **freemium permanente** (opción 2), **sin trial en planes de pago** (decisión 2026-06-21):
- El registro crea una clínica **Free permanente** (sin caducidad), acceso completo dentro de los límites del tier Free (tabla `plans`).
- El upgrade a plan pago implica **cobro inmediato** — no hay trial de 14 días (el Free ya cumple esa función).
- `trial_days = 0` en todos los planes; Paddle checkout sin periodo de prueba.
- El mensaje público: **"Empieza gratis. Sin tarjeta. Sin límite de tiempo."**
- Se elimina todo copy de "14 días de prueba" que implique caducidad al registrarse.
- Se mantiene la lógica de acceso de ADR-008 (Free no-cortesía caducado → read-only) intacta.

### Razones
- Menor fricción de adquisición; el usuario prueba con datos reales y convierte cuando ve valor.
- Coherente con el "sin tarjeta de crédito" que ya aparece en el home.
- Elimina la incoherencia código ↔ marketing actual.

### Consecuencias
- ✅ Mensaje único y veraz en todo el producto.
- ✅ Mejor top-of-funnel.
- ⚠️ Requiere definir límites Free **definitivos** para producción (hoy "ilimitado en dev").
- ⚠️ Hay que actualizar home, pricing, FAQ, onboarding y `auth.free_plan_info`.

---

## ADR-014: Dominio y correo — controclinic.com en iPage

**Fecha:** 2026-06-21
**Estado:** Aceptada

### Contexto
SZ Systems mantiene iPage desde hace 10+ años por dominios de clientes con cientos de buzones
de correo (costo de migrar prohibitive). ControClinic necesita dominio propio, correo
profesional (@controclinic.com) y la app en Hetzner — no en hosting iPage.

### Opciones Consideradas
1. **Cloudflare Registrar** — dominio más barato, sin buzones incluidos.
2. **iPage** — renovación posiblemente más cara, buzones ilimitados @controclinic.com.
3. **iPage solo correo + dominio en CF** — fragmentado, depende de si iPage acepta dominio externo.

### Decisión
- Registrar **`controclinic.com` en iPage** (mismo flujo que otros dominios SZ Systems).
- **Nameservers → Cloudflare** (DNS, CDN, SSL, WAF).
- **App → Hetzner VPS** (`5.78.235.235`) vía Coolify — la web NO se aloja en iPage.
- **Correo → iPage**: buzones `support@`, `noreply@` (SMTP Laravel en producción).
- **Formulario contacto (Web3Forms):** `szystemscorreos@outlook.com` (backend, no visible al usuario).
- Transferencia a Cloudflare Registrar **diferida** a cuando ya no se necesite correo iPage para este dominio.

### Razones
- iPage ya es costo fijo del negocio; marginal solo renovación del dominio.
- Buzones profesionales sin costo extra ni nuevo proveedor.
- Mismo playbook probado: `asonataxela.com`, `clinicaselvalle.com`.

### Consecuencias
- ✅ Operación familiar · correo y DNS alineados con el resto de SZ Systems.
- ⚠️ Renovación posiblemente más cara que CF (~$10/año) — aceptable vs complejidad.
- ⚠️ Checklist DNS: MX iPage + A Hetzner + SPF/DKIM en Cloudflare.

---

## ADR-013: Despliegue en producción — Hetzner + Coolify + Cloudflare

**Fecha:** 2026-06-21
**Estado:** Aceptada

### Contexto
ControClinic debe salir a producción para pruebas reales con dominio propio antes del
lanzamiento. SZ Systems ya opera infraestructura consolidada (workspace `migracion`):
VPS Hetzner `5.78.235.235` + Coolify + Cloudflare.

### Decisión
Desplegar en la **misma infraestructura** siguiendo `migracion/runbooks/03-laravel-a-vps-coolify.md`:
- Dominio registrado en **iPage** (ADR-014); DNS en **Cloudflare**.
- Build `Dockerfile.prod`; `docker-compose.coolify.yml` con servicios **`controclinic-*`**.
- Routing por labels Traefik nativos (Coolify UI).
- Detalle en [DEPLOYMENT.md](DEPLOYMENT.md).

### Consecuencias
- ✅ Reutiliza plantillas y lecciones de producción.
- ⚠️ Respetar prefijado de contenedores y `trustProxies` (evitar cross-serving).

---

## ADR-015: Un plan BD = un tier; Paddle mensual y anual en el mismo registro

**Fecha:** 2026-07-04  
**Estado:** Aceptada

### Contexto
Los tiers (Free, Solo, Práctica, Clínica) se definen en la tabla `plans` con límites y precios. Paddle Billing usa **Productos** y **Prices** separados por intervalo de facturación.

### Opciones Consideradas
1. **Un registro `plans` por tier** con `monthly_price`, `yearly_price`, `paddle_monthly_price_id`, `paddle_yearly_price_id` y un `paddle_product_id` compartido
2. **Dos registros `plans` por tier** (Solo Mensual / Solo Anual) con límites duplicados

### Decisión
**Opción 1:** un solo registro por tier en `plans`. En Paddle:
- 1 **Product** por tier (ej. `ControClinic — Solo`)
- 2 **Prices** en ese producto: recurrente mensual + recurrente anual
- La BD guarda ambos price IDs en el mismo row; checkout elige según `billingCycle`

Los límites (`max_patients`, `max_doctors`, etc.) y `features` son **idénticos** para mensual y anual — solo cambia el precio.

Un **plan distinto en BD** solo cuando el tier es distinto (ej. plan privado con descuento `is_private` + `requires_code`), no por ciclo de cobro.

### Razones
- Evita duplicar límites y features en Admin Plans
- `PaddleEventListener` resuelve el plan con `Plan::findByPaddlePriceId()` (mensual o anual → mismo slug)
- Alineado con Laravel Cashier Paddle y el billing UI existente

### Consecuencias
- ✅ Admin edita un tier una vez; sync a clínicas vía `syncClinicLimits()`
- ✅ Checkout: `$plan->paddlePriceIdForCycle('monthly'|'yearly')`
- ⚠️ Plan promocional con precio distinto requiere **otro row** (slug distinto) con sus propios price IDs Paddle

---

## ADR-016: Flujo de billing Paddle — checkout, cambios de plan y prorrateo

**Fecha:** 2026-07-05
**Estado:** Aceptada

### Contexto
Al probar Paddle sandbox en producción surgieron inconsistencias entre el estado en Paddle, el estado local (`clinics.plan_type` + Cashier `subscriptions`) y la experiencia del usuario. Auditoría completa del flujo (`App\Livewire\App\Billing\Index`, `PaddleEventListener`, `CheckPlanLimits`).

### Hallazgos que motivan la decisión
1. **Alta (clínica sin suscripción):** el webhook `subscription.created` no encontraba la clínica porque no existía el registro `customers` local. Se creaba solo al abrir checkout con `customer` ya presente.
2. **Cambio de plan (`changePlan`):** hacía `subscription->swap()` (PATCH a Paddle con `prorated_next_billing_period`) sin overlay ni confirmación de precio, y sin `try/catch`.
3. **Datos:** Práctica y Clínica compartían el mismo `paddle_product_id` y price IDs → cobro y resolución de plan ambiguos.
4. **Portal:** `Clinic::customerPortalUrl()` no existía (botón "Gestionar pago" rompía).

### Decisión
- **Vincular customer antes del checkout:** `checkout()` llama `createAsCustomer()` si no hay customer, de modo que todo webhook posterior resuelva la clínica (`Cashier::findBillable`).
- **`price_id` único por tier:** cada tier tiene su propio Product + Prices en Paddle. Validación en Admin Plans impide duplicar `paddle_monthly_price_id` / `paddle_yearly_price_id` entre planes.
- **Cambio de plan con confirmación:** los upgrades/downgrades muestran confirmación del precio; el cambio se refleja en Paddle vía Cashier y en la app vía webhook `subscription.updated` (fuente de verdad = Paddle).
- **Prorrateo explícito:** upgrade cobra la diferencia (proración inmediata); downgrade aplica al final del ciclo. No depender del default silencioso.
- **Portal de cliente:** usar `management_urls` de la suscripción de Paddle (update payment method / cancel) en lugar de un método inexistente.
- **Fuente de verdad:** Paddle manda; la app sincroniza en webhooks. `applyPlan()` solo local para reflejar; no invertir el flujo.

### Consecuencias
- ✅ Estado consistente app ↔ Paddle; webhooks idempotentes con customer vinculado.
- ✅ Sin cobros ambiguos entre tiers; Admin no puede duplicar price IDs.
- ⚠️ Requiere crear Product/Prices propios de Clínica en Paddle (los previos coincidían con Práctica).
- ⚠️ El cambio de plan real refleja tras el webhook; la UI debe manejar el estado "pendiente".

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
