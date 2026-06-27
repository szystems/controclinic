# 📝 Tareas — Ruta a v1.0

> Actualizado: 2026-06-27
> **Orden de ejecución:** ver [LAUNCH-PLAN.md](LAUNCH-PLAN.md) (documento maestro).
> Estado: 588 tests · Pint clean · Módulos clínicos completos · Objetivo **1.0.0**

Leyenda: `[ ]` pendiente · `[~]` en progreso · `[x]` hecho

---

## Fase A — Infraestructura y dominio → [LAUNCH-PLAN § A](LAUNCH-PLAN.md#fase-a--infraestructura-y-dominio)

### A.1 — Dominio y correo (Cloudflare + Network Solutions)
- [x] Registrar **controclinic.com** (Network Solutions)
- [x] Añadir zona en Cloudflare · nameservers apuntando a CF
- [x] Email Routing: `support@controclinic.com` → Outlook
- [x] MX, SPF, DKIM, DMARC en Cloudflare (Email Routing)
- [ ] Web3Forms contacto: mantener `szystemscorreos@outlook.com` (no bloqueante)
- [x] TTL / SSL Full (strict) · A + www → VPS

### A.2 — Docker / Coolify (código)
- [x] `Dockerfile.prod` multi-stage
- [x] `docker/php/entrypoint.sh` + `entrypoint-worker.sh`
- [x] `docker-compose.coolify.yml` — servicios prefijados `controclinic-*`
- [x] `docker/nginx/Dockerfile.coolify` — nginx config embebida (fix bind mount Coolify)
- [x] `config/*.php`: `env('VAR') ?: 'default'` · `trustProxies(at:'*')`

### A.3 — Deploy y cutover
- [x] App en Coolify · env secretas (APP_KEY, DB_*)
- [ ] Smoke test prod: login, uploads, queue, scheduler, registro
- [x] FQDN Coolify: `https://controclinic.com,https://www.controclinic.com`
- [x] Cutover DNS A → `5.78.235.235`
- [ ] **Resend** (`MAIL_*`) operativo · probar emails transaccionales
- [ ] Snapshot Hetzner post-deploy

Detalle operativo: [DEPLOYMENT.md](DEPLOYMENT.md)

---

## Fase B — Planes BD fuente única → [LAUNCH-PLAN § B](LAUNCH-PLAN.md#fase-b--planes-en-bd-como-fuente-única)

- [ ] Migración `plan_type` (añadir `practica`, `clinica`) o usar solo `plan_id`
- [ ] Registro: `plan_id` del Free + límites copiados desde `plans`
- [ ] Eliminar fallback `Clinic::PLAN_LIMITS` como fuente principal
- [ ] `trial_days = 0` en todos los planes · sin trial en Paddle checkout
- [ ] Definir límites Free finales en BD (Admin Plans)
- [ ] Plan privado descuento: `is_private` + `requires_code` + prices Paddle
- [ ] UI canje de código en billing
- [ ] Tests planes/límites/registro/upgrade

---

## Fase C — Marca y freemium → [LAUNCH-PLAN § C](LAUNCH-PLAN.md#fase-c--marca-y-mensaje-freemium)

- [ ] Footer: ControClinic by SZ Systems (ADR-011)
- [ ] Emails: firma y remitente alineados
- [ ] Home/pricing: mensaje freemium (sin "14 días") — ADR-012
- [ ] FAQ pricing coherente con Free permanente
- [ ] Checkout Paddle: branding ControClinic
- [x] Login: enlace a registro freemium (`/register`) — no obligar volver al home

---

## Fase D — Paddle → [LAUNCH-PLAN § D](LAUNCH-PLAN.md#fase-d--paddle-monetización)

- [ ] Cuenta Paddle sandbox — SZ Systems
- [ ] Productos `ControClinic — Solo/Práctica/Clínica` · mensual + anual · sin trial
- [ ] `.env` / Coolify: credenciales y price IDs (nunca en repo)
- [ ] Enlazar price IDs en tabla `plans`
- [ ] Checkout billing + webhooks
- [ ] Tests `PaddleEventListener` y billing
- [ ] Sandbox E2E → activar live (Fase F)

---

## Fase E — Legal y marketing → [LAUNCH-PLAN § E](LAUNCH-PLAN.md#fase-e--legal-marketing-y-presencia)

- [ ] Privacy Policy y Terms reales (SZ Systems responsable)
- [ ] `lang/es/legal.php` + `lang/en/legal.php`
- [ ] Marketing home real (testimonios, claims, logos)
- [ ] `lang/{es,en}/public.php` — sitio bilingüe
- [ ] ControClinic en szystems.com

---

## Fase G — Panel Admin operaciones → [LAUNCH-PLAN § G](LAUNCH-PLAN.md#fase-g--panel-admin-operaciones-de-plataforma)

> **Prioridad:** alta · **Momento:** tras A8, antes de F1 · Paralelo con B/C

### G.1 — CRUD Super Admins
- [ ] `Admin/Users/Index` — listar usuarios `is_super_admin=true` (nombre, email, activo, último login)
- [ ] `Admin/Users/Create` — alta super admin (password temporal o generada)
- [ ] `Admin/Users/Edit` — editar nombre, email, `is_active`; reset password por otro admin
- [ ] Eliminar/desactivar — soft delete; guard: no último admin, no auto-eliminación
- [ ] Rutas + nav en `layouts/admin.blade.php`
- [ ] `SuperAdminPolicy` + activity log + tests

### G.2 — Perfil y contraseña (usuario en sesión en `/admin`)
- [ ] `Admin/Profile` — cambiar contraseña (actual + nueva + confirmar)
- [ ] Enlace "Mi cuenta" en menú usuario admin (hoy solo logout)
- [ ] Reutilizar lógica de `App\Livewire\App\Profile\Index::updatePassword()`
- [ ] Tests perfil admin

**Nota:** usuarios de clínica ya tienen cambio de contraseña en `/app/{clinic}/profile` — no duplicar ahí.

---

## Fase F — Go-live → [LAUNCH-PLAN § F](LAUNCH-PLAN.md#fase-f--go-live-release-100)

- [ ] E2E prod: registro → onboarding → uso → upgrade
- [ ] Multi-tenant · 2FA · invitaciones · portal booking
- [ ] Sentry · rate limiters · backups
- [ ] Paddle live · tag `v1.0.0` · apertura pública

---

## ✅ Decisiones cerradas (referencia)

Ver tabla completa en [LAUNCH-PLAN.md § Decisiones cerradas](LAUNCH-PLAN.md#-decisiones-cerradas-2026-06-21).

---

## 🌅 Post-v1 (diferido)

MRR/ARR/churn · SMS/WhatsApp · Social login · Múltiples sedes · App móvil · API · IA

---

## 📌 Historial — Sprints completados (pre-v1)

Sprints A→G · Bloque 0 · v1.3 (2FA) · v1.4 (SOAP, archivos, agenda, catálogo) · F.1–F.10 · G.1–G.5
