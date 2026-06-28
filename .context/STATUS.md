# 📊 Estado Actual del Proyecto

> **Última actualización:** 2026-06-27
> **Fase actual:** 🟡 **Fase A** (~85%) + **Fase G** identificada (ver [LAUNCH-PLAN.md](LAUNCH-PLAN.md))
> **Siguiente paso:** **A8** smoke test prod · **G** CRUD super admins + cambio contraseña en panel admin
> **Métricas:** 588 tests · 1270 assertions · Pint clean
> **Stack:** Laravel 12 · Livewire 3 · Alpine.js · Tailwind · MySQL 8
> **Producción:** ✅ `https://controclinic.com` · Hetzner `5.78.235.235` + Coolify + Cloudflare

---

## 🚀 Progreso hacia v1.0

| Fase | Nombre | Estado |
|------|--------|--------|
| **A** | Infraestructura y dominio | 🟡 ~85% |
| **B** | Planes BD — fuente única | 🔜 |
| **C** | Marca y mensaje freemium | 🔜 |
| **D** | Paddle — monetización | 🔜 |
| **E** | Legal, marketing, szystems.com | 🔜 |
| **G** | Panel Admin — operaciones plataforma | 🔜 |
| **F** | Go-live → **v1.0.0** | 🔜 |

Seguimiento detallado: **[LAUNCH-PLAN.md](LAUNCH-PLAN.md)** · Tareas: [TASKS.md](TASKS.md)

**Decisiones cerradas:** freemium sin trial · dominio NS + DNS Cloudflare · email CF Routing · deploy Coolify · Paddle=SZ Systems · límites en tabla `plans`.

ADRs: **011** (marca) · **012** (freemium) · **013** (deploy) · **014** (dominio/correo — ver nota* en LAUNCH-PLAN)

---

## 🌐 Producción — deploy 2026-06-27

| Item | Estado |
|------|--------|
| Dominio + SSL | ✅ controclinic.com + www |
| Coolify app | ✅ `controclinic:main-ybwfwifzqp47cu1et7pi0vz6` |
| Contenedores | ✅ app, webserver, mysql, redis, queue, scheduler |
| Health check | ✅ `/up` → 200 |
| Login | ✅ `/login` → 200 |
| Seed prod | 🟡 Roles + planes + super admin (DemoClinicSeeder falló — `fake()` no en prod) |
| MAIL saliente | ❌ Resend pendiente |
| Password admin prod | ⚠️ Default del seeder — **bloqueado hasta Fase G2** |

**Gap operativo:** panel `/admin` sin módulo de super admins ni cambio de contraseña en sesión (usuarios clínica sí tienen `/app/profile`).

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

### F.1 — Setup Checklist ✅ (2026-05-20)
- `App\Livewire\App\Dashboard\SetupChecklist` — 6 pasos, anillo SVG, collapsible/dismissible
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
| Pacientes | ✅ Index, Show, Create, Edit, Files | PatientFilesTest, ExportTest, TagsTest |
| Citas | ✅ Index, Show, Create, Edit, Calendar, Agenda diaria | CalendarTest, ExportTest, ScheduleConflictTest |
| Historial médico | ✅ Index, Show, Create (uploader), Edit | MedicalRecordsTest, ExportTest |
| Facturación | ✅ Index, Show, Create, Edit, pagos parciales | InvoicesTest |
| Recetas | ✅ Módulo básico | PrescriptionsTest (por revisar en G.5) |
| Reportes | ✅ Gráficas + CSV export | ReportsTest |
| Staff | ✅ Index, Create, Edit, permisos custom, invitaciones | StaffManagementTest, ExportTest |
| Settings | ✅ General, Catálogo, Plantillas SOAP, Página Pública | RecordTemplatesTest |
| Admin | ✅ Dashboard, Clínicas, Planes, Settings · ❌ Super Admins CRUD · ❌ perfil/contraseña en `/admin` | AdminPanelTest |
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
| Paddle checkout | ▶️ Fase D | Business number obtenido · cuenta SZ Systems |
| CI/CD + Deploy | ✅ Fase A (prod live) | Hetzner + Coolify |
| Admin super users + password | ▶️ Fase G | Bloquea operación segura en prod |
| Métricas MRR/ARR/churn | Post-v1 | Requiere Paddle live con datos reales |
| Social login | Diferido v2 | Incompatible con onboarding actual |
| SMS/WhatsApp | Diferido | Costo operativo, pedir cuando haya clientes |
| Múltiples sedes | Diferido | Schema listo, esperar demanda real |
