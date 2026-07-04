# 📊 Estado Actual del Proyecto

> **Última actualización:** 2026-07-04 (noche)
> **Fase actual:** ✅ **Fase A cerrada** (pendiente solo A11 snapshot + rotación credenciales manual)
> **Siguiente paso:** **Fase C** (copy freemium) + **Fase D** (Paddle sandbox)
> **Métricas:** ~591+ tests · Módulos clínicos completos
> **Stack:** Laravel 12 · Livewire 3 · Alpine.js · Tailwind · MySQL 8
> **Producción:** ✅ `https://controclinic.com` · Hetzner `5.78.235.235` + Coolify + Cloudflare · último deploy `7a5f564` · **Fase B local sin deploy**

---

## 🚀 Progreso hacia v1.0

| Fase | Nombre | Estado |
|------|--------|--------|
| **A** | Infraestructura y dominio | ✅ ~99% (A11 snapshot manual) |
| **B** | Planes BD — fuente única | ✅ código listo · deploy pendiente |
| **C** | Marca y mensaje freemium | 🔜 |
| **D** | Paddle — monetización | 🔜 |
| **E** | Legal, marketing, szystems.com | 🔜 |
| **G** | Panel Admin — operaciones plataforma | ✅ código · 🟡 password prod |
| **F** | Go-live → **v1.0.0** | 🔜 |

Seguimiento detallado: **[LAUNCH-PLAN.md](LAUNCH-PLAN.md)** · Tareas: [TASKS.md](TASKS.md)

**Decisiones cerradas:** freemium sin trial · dominio NS + DNS Cloudflare · email CF Routing · deploy Coolify · Paddle=SZ Systems · límites en tabla `plans`.

ADRs: **011** (marca) · **012** (freemium) · **013** (deploy) · **014** (dominio/correo) · **015** (plans + Paddle pricing)

---

## 🌐 Producción — 2026-07-04

| Item | Estado |
|------|--------|
| Dominio + SSL | ✅ controclinic.com + www |
| Coolify app | ✅ `controclinic:main-ybwfwifzqp47cu1et7pi0vz6` |
| Contenedores | ✅ app, webserver, mysql, redis, queue, scheduler |
| Health check | ✅ `/up` → 200 |
| Smoke test A8 | ✅ Pruebas manuales completadas con éxito |
| Uploads `/storage/` | ✅ Volumen nginx + location |
| MAIL saliente | ✅ Resend SMTP · `php artisan mail:test` |
| Post-deploy auto | ✅ `scripts/post-deploy-health.sh` + systemd watcher (A12) |
| Ops health | ✅ `php artisan ops:health --queue` |
| Password admin prod | ⚠️ Cambiar vía `/admin/profile` |
| Snapshot Hetzner | ⚠️ A11 — pendiente manual en consola |

**Post-deploy:** tras deploy Coolify, Traefik puede dar 503 ~1–6 min; el script A12 reinicia proxy/webserver y recupera solo.

**Últimos deploys:** `7a5f564` archivos UX · `4a70f13` contadores pestañas paciente · `c75f287` staff invite UX · `82107eb` límites plan BD · `dffe0de` infra Fase A.

---

## ✅ Sesión 2026-07-04 (noche) — Fase B planes BD

### Fuente única de límites
- **`Clinic::getPlanLimits()`** lee plan vía `plan_id` / slug; registro copia límites Free
- **Migración** `2026_07_04_180000`: `plans.access_code` + `clinics.plan_type` VARCHAR(50) (`practica`, `clinica`, etc.)
- **PlansSeeder**: Free `max_staff=1` · tiers Práctica/Clínica · plan privado `solo-estudiante` (código `CC-ESTUDIANTE`)
- **Paddle**: `applyPlan()` en listener y billing; checkout sin trial (ADR-012)

### Admin + Billing UI
- **Admin Plans Edit**: toggles plan privado + código de acceso
- **Billing**: input canje código → desbloquea plan privado en lista; checkout bloqueado sin unlock
- **Traducciones** ES/EN billing + admin

### Tests
- PlanLimitsTest (Free staff=1, practica/clinica slugs)
- BillingTest (promo válido/inválido, checkout privado sin unlock)

**Deploy pendiente:** commit + migrate + re-seed plans en prod.

---

## ✅ Sesión 2026-07-04 (tarde) — UX paciente + staff + archivos

### Staff / planes
- **Invitaciones staff** (`82107eb`, `c75f287`) — límites leen Admin Plans vía `resolvePlan()`; invitaciones pendientes cuentan al cupo; aviso cuando slot ocupado; roles siempre visibles
- **`php artisan clinics:backfill-plan-ids`** — ejecutado en prod (4 clínicas)

### Vista paciente
- **Contadores en pestañas** (`4a70f13`) — Citas, Historial, Recetas, Archivos, Facturación, Actividad (badges circulares; Datos y Notas sin contador)
- **Archivos: thumbnails** (`5b683a7`) — miniatura real para imágenes (ruta autenticada); preview cliente al subir; icono para PDF/otros
- **Archivos: layout tarjetas** (`7a5f564`) — botones Ver/Descargar alineados al fondo del card (flex `mt-auto`)

---

## ✅ Sesión 2026-07-04 (mañana) — Smoke test A8 + infra

### Infra / estabilidad
- **Fase A infra** (`dffe0de`) — `mail:test`, `ops:health --queue`, post-deploy hook + systemd watcher
- **Uploads prod** — volumen `app_storage` en nginx + `location /storage/`
- **HTTPS post-deploy** — A12 automatizado (health + restart Traefik)

### Onboarding y locale
- **Detección locale silenciosa** (`ClinicLocaleResolver`)
- **Onboarding pantalla negra** — fix Driver.js en wizard; stepper móvil
- **Plantillas consulta** (`8882d3a`) — paso checklist + guía setup + auto-carga default

### UX smoke test (A8) — ✅ verificado en prod
- Zonas horarias · tipos documento · menú puntitos tablas · perfil clínica (`a5952ad`)

---

## 🟡 Fase B — Planes BD (en curso ~45%)

| Item | Estado |
|------|--------|
| **ADR-015** — 1 row `plans` = 1 tier; mensual+anual = 2 Paddle price IDs | ✅ |
| `Plan::findByPaddlePriceId()`, `paddlePriceIdForCycle()`, `registrationAttributesFrom()` | ✅ |
| `Clinic::getPlanLimits()` — plan BD → columnas clínica → free plan → legacy | ✅ |
| Registro copia límites del plan Free | ✅ |
| `PlansSeeder` — `trial_days = 0`; Free citas/mes = 5 | ✅ |
| Factory/tests auto-seed `plans` | ✅ |
| `resolvePlan()`, backfill, sync Admin, invitaciones pendientes | ✅ |
| Eliminar por completo `Clinic::PLAN_LIMITS` | 🔜 (deprecated) |
| Plan privado + código descuento (B6–B7) | 🔜 |
| Paddle sandbox — crear products/prices | 🔜 Fase D |

---

## ✅ Fase G — Panel Admin (código — `4b9f350`)

| Item | Estado |
|------|--------|
| CRUD Super Admins | ✅ |
| Perfil admin + cambio contraseña | ✅ |
| Verificar en prod + rotar password | 🟡 pendiente usuario |

---

## ✅ Sprint G — COMPLETADO (2026-05-25)

### G.1 — Branding público/auth ✅
- Nav público muestra solo logo-imagen cuando hay logo configurado
- Auth views usan `<x-app-logo>` (lee `branding.logo_url`)

### G.2 — Pagos parciales facturación ✅
- `InvoicePartialPaymentsTest` — 19 tests · modal pago, saldo pendiente, cierre automático

### G.3 — Admin métricas ✅
- KPIs + gráfica semanal 12 semanas + último login owner
- Fix: `last_login_at` ahora actualiza en evento `Login`

### G.4 — Tests SetupChecklist + EmptyStates ✅
- 19 tests nuevos (11 + 8)

### G.5 — Módulo Recetas ✅ (ya estaba completo)
- 14 tests en `PrescriptionsTest` · PDF, folio, QR, policy

---

## ✅ Sprint F — UX & Onboarding (COMPLETADO 2026-05-21)

### F.1 — Setup Checklist ✅ (2026-05-20, ampliado 2026-07-04)
- `App\Livewire\App\Dashboard\SetupChecklist` — **7 pasos**, anillo SVG, collapsible/dismissible
- Pasos: logo · horarios · **plantilla consulta** · paciente · cita · staff · página pública
- Solo visible para owner. Traducciones `lang/{es,en}/setup_checklist.php`

### F.2 — Empty States ✅ (2026-05-20)
- `<x-empty-state>` — 10 iconos, compact, CTA con @can, bullets i18n
- Aplicado en 6 módulos: patients, appointments, invoices, prescriptions, staff, medical-records

### F.3 — Página pública enriquecida ✅ (2026-05-20)
- Migración: `public_description`, `public_cover_image_url`, `public_services`, `public_show_doctors`, SEO fields
- Tab "Página Pública" en Settings (owner): cover image, descripción, servicios, equipo, SEO
- `/c/{slug}` convertida en landing: hero, About, Servicios grid, Equipo médico, CTA booking
- 21 tests (SettingsPublicPageTest + PublicBookingTest)

### F.4 — Tour guiado Driver.js ✅ (2026-05-20)
- `driver.js` v1.4.0 · `App\Livewire\App\Tour\Launcher` — autoStart, completeTour, skipTour, replayTour
- Tour por rol: owner/doctor (8 pasos), assistant (5), secretary/receptionist (4)
- Persistencia en `users.preferences` (3 capas: DB + localStorage + window.TOUR_CONFIG)

### F.5 — Ayuda contextual + /help ✅ (2026-05-20)
- `<x-help-banner>` colapsable por módulo (localStorage) · `<x-tooltip>` Alpine.js 4 posiciones
- `App\Livewire\App\Help\Index` + `Show` (8 módulos) · Botón flotante móvil
- Páginas de error 403/404/500/503 con layout ControClinic

### F.6 — Onboarding mejorado ✅ (2026-05-20)
- Upload de logo drag-and-drop en paso 3 · Botón "Saltar este paso" en pasos 2-4
- Fix `is_manual_plan=true` en registro

### F.7 — Custom domain ✅ (2026-05-20)
- `clinics.custom_domain` + verificación TXT · Middleware `ResolveCustomDomain`
- UI en Settings tab Página Pública (solo Enterprise) · Cache 5 min
- 8 tests (CustomDomainTest)

### F.8 — Demo data toggle ✅ (2026-05-20)
- `is_demo` bool en patients/appointments/medical_records/invoices/prescriptions
- Comando `clinic:seed-demo` + `--clear` · `DemoDataBanner` solo para owner
- 8 tests (DemoDataTest)

### F.9 — Skeleton screens ✅ (2026-05-20)
- `x-skeleton-table`, `x-skeleton-card`, `x-skeleton-list` · NProgress bar Alpine.js en layout
- Aplicado en: patients/index, appointments/index, appointments/calendar, patients/files

### F.10 — Atajos de teclado ✅ (2026-05-21)
- `App\Livewire\App\KeyboardShortcuts` · navMap filtrado por permisos en PHP
- `g+d/p/a/c/i/r` navega, `?` abre modal, Esc cierra
- Botón flotante `fixed bottom-5 right-5` (hidden md:flex) · dark mode
- Fix: `x-init` + `$cleanup` evita listeners duplicados · `$clinic` directo del route

---

## ✅ Historial de sprints

### Sprint A — Admin Settings (SEO + Branding) ✅
- `app_setting()` helper · Tab SEO (GA/GTM, OG image) · Branding dinámico en todos los layouts

### Sprint B — Landing pública + 4 Tiers ✅
- 4 planes: Free $0 / Solo $19 / Práctica $49 / Clínica $99 / Enterprise
- Sitemap, robots.txt, `Plan::scopePublic()`

### Sprint C — Pulido de Listados ✅ (2026-05-08)
- Patients: filtros edad/cita futura/deudores · columnas consultas/próxima cita/saldo
- Appointments: filtro rango fechas + createdVia · columnas precio/facturado
- MedicalRecords: filtro por adjuntos · Invoices: filtro vencidas/método pago
- Staff: withCount citas+consultas · Tests migrados SQLite → MySQL (484/484)

### Bloque 0 — Hardening + Forward-Compat ✅ (2026-04-30)
- 7 migraciones preventivas (nullable, aditivas): `parent_clinic_id`, `branch_id`, etc.
- Policies (Patient, Appointment, MedicalRecord) · SoftDeletes en todos los modelos core
- Backup automático diario (spatie/laravel-backup, DB → local+S3) · Sentry configurado
- Rate limiters (api/global/sensitive/webhook) · `TenantMiddleware` + `SetLocale` persistentes

### v1.4 — Features clínicas ✅ (2026-05-03)
- **Plantillas SOAP**: CRUD en Settings, integración en MedicalRecords\Create, 10 tests
- **Archivos del paciente**: `patient_files`, stream seguro, lightbox, mini-uploader
- **Agenda diaria**: tabla multi-doctor, slots 30min 07:00-20:30, filtro por doctor
- **Catálogo de servicios**: CRUD en Settings, autocompletado en facturas, modal sugerencia
- **Exportación ZIP**: CSV completo de todos los datos de la clínica

### v1.3 — 2FA TOTP ✅ (2026-05-12)
- TOTP con QR + clave manual · 8 recovery codes de un solo uso
- Middleware `EnsureTwoFactorAuthenticated` en rutas sensibles

---

## 📦 Módulos implementados (referencia rápida)

| Módulo | Estado | Tests |
|--------|--------|-------|
| Pacientes | ✅ Index, Show (pestañas con contadores), Create, Edit, Files (thumbnails) | PatientFilesTest, PatientShowTabsTest, ExportTest, TagsTest |
| Citas | ✅ Index, Show, Create, Edit, Calendar, Agenda diaria | CalendarTest, ExportTest, ScheduleConflictTest |
| Historial médico | ✅ Index, Show, Create (uploader), Edit | MedicalRecordsTest, ExportTest |
| Facturación | ✅ Index, Show, Create, Edit, pagos parciales | InvoicesTest |
| Recetas | ✅ Módulo básico | PrescriptionsTest (por revisar en G.5) |
| Reportes | ✅ Gráficas + CSV export | ReportsTest |
| Staff | ✅ Index, Create (UX límites/invitaciones), Edit, invitaciones | StaffManagementTest, ExportTest |
| Settings | ✅ General, Catálogo, Plantillas SOAP (+ guía setup), Página Pública | RecordTemplatesTest |
| Admin | ✅ Dashboard, Clínicas, Planes, Settings, **Super Admins CRUD**, **/admin/profile** | AdminPanelTest, AdminSuperAdminsTest |
| Perfil | ✅ Perfil, 2FA, Transferencia ownership | ProfileTest, ProfileActivityTest |
| Agenda | ✅ Bloqueo horarios (doctor_unavailabilities) | DoctorScheduleTest |
| Auditoría | ✅ AuditLog Index filtros + paginación | AuditLogTest |
| Búsqueda global | ✅ Cmd+K modal Alpine | GlobalSearchTest |
| Portal público | ✅ /c/{slug} landing + wizard booking 3 pasos | PublicBookingTest |
| Atajos teclado | ✅ g+nav, ? modal, botón flotante | KeyboardShortcutsTest (pendiente G.4) |
| Notificaciones email | ✅ 5 Mailables + Job + Scheduler hourly | AppointmentNotificationsTest |
| Confirmación citas | ✅ Sin login, token único | AppointmentConfirmationTest |

---

## 🔒 Backlog diferido

| Item | Estado | Razón |
|------|--------|-------|
| Post-deploy Traefik restart | ✅ A12 automatizado | Script + systemd watcher en VPS |
| Paddle checkout | ▶️ Fase D | Business number obtenido · cuenta SZ Systems |
| CI/CD + Deploy | ✅ Fase A (prod live) | Hetzner + Coolify |
| Admin password prod | 🟡 Verificar | G2 implementado — cambiar en `/admin/profile` |
| Métricas MRR/ARR/churn | Post-v1 | Requiere Paddle live con datos reales |
| Social login | Diferido v2 | Incompatible con onboarding actual |
| SMS/WhatsApp | Diferido | Costo operativo, pedir cuando haya clientes |
| Múltiples sedes | Diferido | Schema listo, esperar demanda real |
| Tour plantillas | Opcional | Mencionar plantillas en Driver.js (complemento al checklist) |
