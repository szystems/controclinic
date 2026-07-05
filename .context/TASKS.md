# 📝 Tareas — Ruta a v1.0

> Actualizado: 2026-07-04 (tarde)
> **Orden de ejecución:** ver [LAUNCH-PLAN.md](LAUNCH-PLAN.md) (documento maestro).
> **Fase actual:** B (planes BD) · Fase A cerrada salvo tareas manuales
> Estado: ~591+ tests · Objetivo **1.0.0** · Prod deploy `7a5f564`

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
- [x] Volumen nginx `app_storage` + `location /storage/` (uploads prod)
- [x] `config/*.php`: `env('VAR') ?: 'default'` · `trustProxies(at:'*')`

### A.3 — Deploy y cutover
- [x] App en Coolify · env secretas (APP_KEY, DB_*)
- [x] Smoke test prod completo (A8 — ver desglose abajo)
- [x] FQDN Coolify: `https://controclinic.com,https://www.controclinic.com`
- [x] Cutover DNS A → `5.78.235.235`
- [x] **Resend** (`MAIL_*`) operativo · `php artisan mail:test` (A10)
- [ ] Snapshot Hetzner post-deploy (A11 — manual en consola)
- [x] Script + systemd post-deploy Traefik health (A12)

Detalle operativo: [DEPLOYMENT.md](DEPLOYMENT.md)

### A.8 — Smoke test prod (desglose) ✅
- [x] Login / HTTPS / health `/up`
- [x] Registro · onboarding · locale · perfil clínica
- [x] Uploads archivos · plantillas consulta · UX listados
- [x] Queue worker + scheduler (`ops:health --queue`)
- [x] Emails transaccionales Resend SMTP
- [x] Staff invitaciones · vista paciente · archivos (pruebas manuales 2026-07-04)
- [ ] Rotar credenciales expuestas en chat (Resend, admin password)

### A.9 — UX prod desplegada (2026-07-04 tarde)
- [x] Contadores pestañas paciente (`4a70f13`)
- [x] Thumbnails archivos paciente (`5b683a7`)
- [x] Tarjetas archivos — botones al fondo (`7a5f564`)
- [x] Staff invite — límites plan + aviso invitación pendiente (`82107eb`, `c75f287`)

---

## Fase B — Planes BD fuente única → [LAUNCH-PLAN § B](LAUNCH-PLAN.md#fase-b--planes-en-bd-como-fuente-única)

> **Código completo** — pendiente deploy + `migrate` + `db:seed --class=PlansSeeder` en prod

- [x] `Clinic::resolvePlan()` + `getPlanLimits()` desde BD — `emergencyPlanLimits()` solo emergencia
- [x] Registro: `plan_id` + límites copiados (`Plan::registrationAttributesFrom`)
- [x] `clinics:backfill-plan-ids` + ejecutado en prod
- [x] Admin Plans: `syncClinicLimits()` + campos `is_private`, `requires_code`, `access_code`
- [x] Invitaciones pendientes cuentan en límite staff/doctor
- [x] `PlansSeeder` trial_days=0 · Free max_staff=1 · tiers practica/clinica · `solo-estudiante`
- [x] ADR-015 Paddle mensual/anual mismo row
- [x] `Plan::findByPaddlePriceId()` · `billingVisibleFor()` · `findByAccessCode()`
- [x] Billing: canje código promo + gate checkout plan privado
- [x] `CheckPlanLimits`: downgrade vía `applyPlan(free)` 
- [x] Tests: PlanLimitsTest + BillingTest (promo, practica/clinica)
- [ ] Eliminar por completo `Clinic::emergencyPlanLimits()` cuando prod siempre tenga plans (opcional)

---

## Fase C — Marca y freemium → [LAUNCH-PLAN § C](LAUNCH-PLAN.md#fase-c--marca-y-mensaje-freemium)

> **Completada** — deploy pendiente commit

- [x] Footer público + app: `ControClinic · A product of SZ Systems · Victoria, BC` (ADR-011)
- [x] Emails: firma en layout mail + `mail.all_rights_reserved`
- [x] Home/pricing: freemium ADR-012 vía `lang/*/public.php` (sin "14 días")
- [x] FAQ pricing: plan Free permanente + `#faq` anchor
- [x] `/pricing` → `Plan::active()->public()` (ya verificado + tests)
- [x] Login: enlace registro freemium
- [ ] Checkout Paddle branding (Fase D)

---

## Fase D — Paddle → [LAUNCH-PLAN § D](LAUNCH-PLAN.md#fase-d--paddle-monetización)

- [x] Cuenta Paddle sandbox — SZ Systems
- [x] Productos `ControClinic — Solo/Práctica/Clínica/Friendly` · mensual + anual · sin trial
- [x] `.env` / Coolify: credenciales y price IDs (nunca en repo)
- [x] Enlazar price IDs en tabla `plans` (Admin Planes)
- [x] Webhook + firma + Website Approval + Default payment link
- [x] Checkout billing (alta) E2E funcionando
- [ ] **Optimización ADR-016** (auditoría 2026-07-05):
  - [ ] `changePlan()` con confirmación de precio + manejo de errores
  - [ ] Clínica: Product/Prices propios en Paddle + validación IDs únicos en Admin
  - [ ] `Clinic::customerPortalUrl()` real (management_urls)
  - [ ] `checkout()` vía `Cashier::api()`; política de prorrateo explícita
  - [ ] `resumeSubscription()` cubrir `paused`/grace; limpiar config legacy
- [ ] Tests `PaddleEventListener` y billing (swap, cancel, webhook)
- [ ] Verificar webhooks E2E con evento real (Paddle → View logs)
- [ ] Sandbox E2E completo → activar live (Fase F)

---

## Fase E — Legal y marketing → [LAUNCH-PLAN § E](LAUNCH-PLAN.md#fase-e--legal-marketing-y-presencia)

- [ ] Privacy Policy y Terms reales (SZ Systems responsable)
- [ ] `lang/es/legal.php` + `lang/en/legal.php`
- [ ] Marketing home real (testimonios, claims, logos)
- [ ] `lang/{es,en}/public.php` — sitio bilingüe
- [ ] ControClinic en szystems.com

---

## Fase G — Panel Admin operaciones → [LAUNCH-PLAN § G](LAUNCH-PLAN.md#fase-g--panel-admin-operaciones-de-plataforma)

> **Estado código:** ✅ implementado (`4b9f350`) · **Pendiente:** verificar en prod + cambiar password default

### G.1 — CRUD Super Admins
- [x] `Admin/Users/Index` — listar usuarios `is_super_admin=true`
- [x] `Admin/Users/Create` — alta super admin
- [x] `Admin/Users/Edit` — editar nombre, email, `is_active`; reset password
- [x] Eliminar/desactivar — guards último admin / auto-eliminación
- [x] Rutas + nav en `layouts/admin.blade.php`
- [x] Tests `AdminSuperAdminsTest`

### G.2 — Perfil y contraseña (usuario en sesión en `/admin`)
- [x] `Admin/Profile` — cambiar contraseña
- [x] Enlace "Mi cuenta" en menú usuario admin
- [x] Tests perfil admin
- [ ] Cambiar password default super admin en prod

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

Sprints A→G · Bloque 0 · v1.3 (2FA) · v1.4 (SOAP, archivos, agenda, catálogo) · F.1–F.10 · G.1–G.5 · **2026-07-04:** Fase A cerrada · UX paciente/staff/archivos · inicio Fase B
