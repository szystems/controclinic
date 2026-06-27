# 🚀 Plan de acción — ControClinic v1.0

> **Documento maestro de seguimiento** hasta el lanzamiento público.
> Actualizado: 2026-06-27 · Objetivo: **release 1.0.0**
> Detalle por área: [TASKS.md](TASKS.md) · Infra: [DEPLOYMENT.md](DEPLOYMENT.md) · ADRs: [DECISIONS.md](DECISIONS.md)

---

## Progreso general

| Fase | Nombre | Estado | Bloquea lanzamiento |
|------|--------|--------|---------------------|
| **A** | Infraestructura y dominio | 🟡 ~85% | Parcial (mail saliente + smoke test) |
| **B** | Planes BD — fuente única | 🔜 pendiente | Sí (límites y billing) |
| **C** | Marca y mensaje freemium | 🔜 pendiente | Sí (confianza / conversión) |
| **D** | Paddle — monetización | 🔜 pendiente | Sí (cobro real) |
| **E** | Legal, marketing y szystems.com | 🔜 pendiente | Sí (apertura pública) |
| **G** | Panel Admin — operaciones plataforma | 🔜 pendiente | Sí (seguridad operativa prod) |
| **F** | Go-live y release 1.0.0 | 🔜 pendiente | Sí |

**Siguiente paso:** **A8** (smoke test prod) en paralelo con **G** (admin usuarios + contraseña). Luego **A10** (Resend) y **B/C**.

---

## ✅ Decisiones cerradas (2026-06-21)

| Tema | Decisión | ADR |
|------|----------|-----|
| Marca legal vs producto | **SZ Systems** cobra; **ControClinic** es la marca visible | ADR-011 |
| Modelo de adquisición | **Freemium permanente** — registro en plan Free sin caducidad | ADR-012 |
| Trial en planes de pago | **No** — el Free ya es la prueba; upgrade = cobro inmediato | ADR-012 |
| Límites de tiers | Tabla **`plans`** en BD (Admin Plans); mensual + anual por tier | — |
| Plan con descuento | Plan **`is_private` + `requires_code`** en BD (+ price IDs Paddle propios) | pendiente impl. |
| Dominio | **`controclinic.com`** registrado (Network Solutions) · DNS en Cloudflare | ADR-014* |
| DNS / app | **Cloudflare** (DNS, SSL, WAF) + **Hetzner/Coolify** (app en prod) | ADR-013 |
| Correo recibir | **Cloudflare Email Routing** — `support@` → Outlook | — |
| Correo contacto (Web3Forms) | `szystemscorreos@outlook.com` (backend, no visible) | — |
| Correo transaccional app | **Resend** (pendiente) — `noreply@controclinic.com` | — |
| Paddle | Cuenta a nombre de **SZ Systems**; productos `ControClinic — {Plan}` | ADR-011 |

---

## Orden de ejecución

Las fases **A** y **B** pueden avanzar en paralelo. **C** y **D** requieren **B** parcialmente.
**E** puede hacerse en paralelo desde el inicio (contenido/legal). **F** es la secuencia final.

```
A (dominio + deploy) ──┬──► F (go-live)
B (planes BD) ─────────┤
C (marca + copy) ──────┤
G (admin plataforma) ──┤   ← antes de F; paralelo con A8/B/C
D (Paddle) ────────────┤
E (legal + marketing) ─┘
```

---

## Fase A — Infraestructura y dominio

> Objetivo: app en producción accesible (primero sslip.io, luego controclinic.com).
> Runbook: `~/proyectos/migracion/runbooks/03-laravel-a-vps-coolify.md`

| # | Tarea | Responsable | Estado |
|---|-------|-------------|--------|
| A1 | Registrar **controclinic.com** (Network Solutions) | Usuario | [x] |
| A2 | Añadir dominio en **Cloudflare** · DNS activo · MFA | Usuario | [x] |
| A3 | Correo recibir: **Cloudflare Email Routing** (`support@` → Outlook) | Usuario | [x] |
| A4 | DNS Cloudflare: MX/SPF/DKIM/DMARC (Email Routing) | Usuario | [x] |
| A5 | Código: `Dockerfile.prod`, `entrypoint.sh`, `docker-compose.coolify.yml` (`controclinic-*`) | Dev | [x] |
| A6 | Código: `config/*.php` con `env() ?: default` · `trustProxies(at:'*')` | Dev | [x] |
| A7 | Coolify: app ControClinic · env secretas · deploy exitoso | Dev | [x] |
| A8 | Smoke test prod: login, registro, uploads, queue, scheduler, locale, HTTPS | Dev | [ ] |
| A9 | DNS **A/@/www → 5.78.235.235** (proxied) · FQDN en Coolify | Usuario+Dev | [x] |
| A10 | Configurar **Resend** (`MAIL_*`) en Coolify · probar reset password / email cita | Dev | [ ] |
| A11 | Snapshot Hetzner post-deploy exitoso | Usuario | [ ] |

**Producción (2026-06-27):** `https://controclinic.com/up` → 200 · 6 contenedores `controclinic-*` Up · Super Admin seedeado (`admin@controclinic.com` — **cambiar password vía Fase G2**).

**Web3Forms (formulario contacto):** mantener `szystemscorreos@outlook.com` — no bloqueante.

---

## Fase B — Planes en BD como fuente única

> Objetivo: cambiar límites/precios/features solo en Admin Plans o seeder; UI y enforcement automáticos.
> Ver análisis técnico en conversación 2026-06-21.

| # | Tarea | Estado |
|---|-------|--------|
| B1 | Migración: ampliar `clinics.plan_type` (incluir `practica`, `clinica`) o deprecar enum a favor de solo `plan_id` | [ ] |
| B2 | Registro: asignar `plan_id` del plan Free + copiar límites desde BD | [ ] |
| B3 | `Clinic::getPlanLimits()`: priorizar siempre `plan` vía `plan_id`; eliminar fallback `PLAN_LIMITS` | [ ] |
| B4 | `trial_days = 0` en seeder y Admin; no pasar trial a checkout Paddle | [ ] |
| B5 | Ajustar límites Free en BD (Admin Plans / seeder) — valores finales de producción | [ ] |
| B6 | Crear plan privado con descuento (`is_private=true`, `requires_code=true`) + price IDs Paddle | [ ] |
| B7 | Implementar canje de código → mostrar plan en billing + checkout | [ ] |
| B8 | Tests: registro con `plan_id` · upgrade cambia límites · enum/slug practica/clinica | [ ] |

---

## Fase C — Marca y mensaje freemium

| # | Tarea | Estado |
|---|-------|--------|
| C1 | Footer app + público: **ControClinic · A product of SZ Systems · Victoria, BC** | [ ] |
| C2 | Emails transaccionales: firma y remitente alineados con marca | [ ] |
| C3 | Home + pricing: quitar "14 días de prueba" → **"Empieza gratis. Sin tarjeta. Sin límite de tiempo."** | [ ] |
| C4 | FAQ pricing: reescribir preguntas sobre trial / plan Free | [ ] |
| C5 | Verificar `/pricing` y billing leen tiers solo de `Plan::active()->public()` | [ ] |
| C6 | **Login → registro:** enlace "Crear clínica gratis" en `/login` (hoy solo desde home) | [x] |

---

## Fase D — Paddle (monetización)

> Business number obtenido. Credenciales **solo en `.env`** / Coolify — nunca en repo ni chat.

| # | Tarea | Estado |
|---|-------|--------|
| D1 | Crear cuenta Paddle **sandbox** — entidad **SZ Systems** | [ ] |
| D2 | Productos: `ControClinic — Solo / Práctica / Clínica` · prices mensual + anual · **sin trial** | [ ] |
| D3 | Plan descuento: prices Paddle separados (si aplica Fase B6) | [ ] |
| D4 | `.env` / Coolify: seller ID, tokens, webhook secret, price IDs | [ ] |
| D5 | Enlazar `paddle_*_price_id` en BD (`PlansSeeder` o Admin) | [ ] |
| D6 | Checkout billing + webhooks (alta, pago, fallo, cancelación) | [ ] |
| D7 | Logo ControClinic en checkout Paddle | [ ] |
| D8 | Tests billing + `PaddleEventListener` | [ ] |
| D9 | Probar flujo completo en **sandbox** sobre staging/prod | [ ] |
| D10 | Activar Paddle **live** cuando checklist F esté verde | [ ] |

---

## Fase E — Legal, marketing y presencia

> No bloquea deploy ni desarrollo; **sí bloquea apertura pública**.

| # | Tarea | Estado |
|---|-------|--------|
| E1 | Privacy Policy real (datos médicos) | [ ] |
| E2 | Terms of Service definitivos (SZ Systems como responsable) | [ ] |
| E3 | Actualizar `lang/es/legal.php` + crear `lang/en/legal.php` | [ ] |
| E4 | Home: testimonios/claims/logos reales o eliminar placeholders | [ ] |
| E5 | Extraer copy público a `lang/{es,en}/public.php` | [ ] |
| E6 | Sección ControClinic destacada en **szystems.com** | [ ] |
| E7 | Confirmar FAQ descuento estudiantes (¿real? ¿código Paddle o plan privado?) | [ ] |

---

## Fase G — Panel Admin: operaciones de plataforma

> Objetivo: operación segura del SaaS en producción — gestionar super admins y cuenta propia desde `/admin`.
> **Momento ideal:** tras **A8** (smoke test), **antes de F1** (E2E go-live). Paralelo con **B/C**.
> **Gap detectado (2026-06-27):** el panel admin tiene Dashboard, Clínicas, Planes y Settings, pero **no** CRUD de usuarios super admin ni cambio de contraseña del usuario en sesión. Los usuarios de clínica **sí** tienen cambio de contraseña en `/app/profile` (`App\Livewire\App\Profile\Index`).

| # | Tarea | Estado |
|---|-------|--------|
| G1 | **CRUD Super Admins** — `Admin/Users/Index`, `Create`, `Edit` · listar `is_super_admin=true` | [ ] |
| G2 | Crear super admin: nombre, email, password temporal · email verificado · activity log | [ ] |
| G3 | Editar super admin: nombre, email, `is_active` · reset password (admin → otro usuario) | [ ] |
| G4 | Eliminar/desactivar super admin · soft delete · no eliminar último admin ni a uno mismo | [ ] |
| G5 | **Perfil admin** — `Admin/Profile` o sección en layout admin: cambiar contraseña (actual + nueva + confirmar) | [ ] |
| G6 | Nav admin: enlace "Mi cuenta" / "Cambiar contraseña" en menú usuario (hoy solo logout) | [ ] |
| G7 | Policy `SuperAdminPolicy` · permisos · tests `AdminSuperAdminsTest` + perfil | [ ] |
| G8 | Traducciones `lang/{es,en}/admin.php` · i18n completo | [ ] |

**Notas técnicas:**
- Rutas bajo `Route::prefix('admin')` + middleware `EnsureIsAdmin`.
- Patrón Livewire: `app/Livewire/Admin/Users/{Index,Create,Edit}.php` (igual que Clinics/Plans).
- Reutilizar validación de `App\Livewire\App\Profile\Index::updatePassword()` en G5.
- Super admins pueden no tener `clinic_id`; no depender de rutas `/app/{clinic}/profile`.

---

## Fase F — Go-live (release 1.0.0)

| # | Tarea | Estado |
|---|-------|--------|
| F1 | E2E prod: registro → verificación email → onboarding → dashboard | [ ] |
| F2 | E2E prod: crear paciente/cita → topar límite Free → upgrade Paddle sandbox | [ ] |
| F3 | Multi-tenant: 2 clínicas aisladas en prod | [ ] |
| F4 | 2FA, invitaciones staff, portal público booking en prod | [ ] |
| F5 | Sentry + rate limiters + backups verificados | [ ] |
| F6 | Paddle sandbox → **live** | [ ] |
| F7 | Checklist firmado · tag **`v1.0.0`** · anuncio / apertura pública | [ ] |

---

## Qué NO entra en v1.0 (post-lanzamiento)

- Métricas MRR/ARR/churn en Admin
- SMS/WhatsApp
- Social login
- Múltiples sedes
- App móvil / API pública / IA

---

## Cómo actualizar este documento

1. Al completar una tarea: cambiar `[ ]` → `[x]` y actualizar tabla **Progreso general**.
2. Actualizar **Siguiente paso** con el primer ítem pendiente.
3. Reflejar hitos en [STATUS.md](STATUS.md) (fecha + fase completada).
4. Detalle técnico adicional → [TASKS.md](TASKS.md).
