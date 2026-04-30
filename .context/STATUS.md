# 📊 Estado Actual del Proyecto

> **Última actualización:** 2026-05-01
> **Fase actual:** v1.0 — Bloque 0 completado. Fase 3C (Permisos Personalizados) completada. Próximo: Bloque 1 Core App
> **Enfoque:** SaaS-First
> **Métricas:** 335 tests / 763 asserts · Pint clean · PHPStan level 5 (con baseline) · npm build OK

## ✅ Fase 3C — Permisos Personalizados (2026-05-01) ✅

- [x] `PERMISSION_CATALOG` en `Staff\Edit` agrupado por módulo (patients, appointments, records, settings, users, billing, reports)
- [x] UI de toggles con indicador "rol" para permisos heredados (no editables)
- [x] Botón "Restaurar permisos del rol" con confirmación (elimina todos los directos + Activity Log)
- [x] `restoreRolePermissions()` method con Activity Log `permissions_restored`
- [x] Activity Log `permissions_updated` en `save()` al cambiar permisos directos
- [x] Nombres legibles de permisos via `lang/{es,en}/permissions.php` (en lugar de nombres técnicos)
- [x] 7 tests Feature (asignar, heredados no duplicados, fuera de catálogo ignorados, revocar, restaurar, permisos insuficientes, activity log)
- [x] Suite completa: 335/335 verde

---

## ✅ DevOps / Infraestructura (2026-05-01) ✅

- [x] Canal `json` + `daily_json` en `config/logging.php` para logs estructurados en producción (Monolog `JsonFormatter`)
- [x] `docker/deploy.sh` — script de deploy con mantenimiento, migrations, optimize, queue:restart, health check
- [x] `.env.production.example` actualizado con `LOG_STACK=daily_json`

---



- [x] `/health` endpoint — devuelve JSON con estado de DB, cache, storage, app, env. 200 ok / 503 degraded.
- [x] Checkbox de consentimiento en formulario de registro (Términos + Privacidad). Guarda `terms_accepted_at` en users.
- [x] Traducciones ES/EN: `terms_acceptance`, `terms_link`, `privacy_link`, `terms_required`
- [x] Cookie banner en portal público (`/c/{slug}`) con Alpine.js + localStorage. GDPR mínimo.
- [x] `.env.production.example` documentado con checklist de producción
- [x] Suite completa: 332/332 verde

---

## 🧱 Bloque 0.2 Forward-Compat DB (2026-04-30) ✅

- [x] 7 migraciones aditivas aplicadas (todas reversibles, validado con rollback + migrate)
- [x] `clinics`: `parent_clinic_id`, `legal_entity_id`, `data_retention_years`
- [x] `users`: 2FA (`two_factor_secret/recovery_codes/confirmed_at` encrypted+hidden), `signature_path`, `last_seen_at`, `preferences`, `terms_accepted_at`
- [x] `patients`: `internal_notes`, `portal_user_id`, `external_id`, `consent_signed_at`, `marketing_opt_in`
- [x] `appointments`: `branch_id`, `consultation_price/discount`, `is_billable`, `confirmation_token`, `confirmed_via`, `telemedicine_link/provider`, `pre_consultation_form_id`, `parent_appointment_id`, `created_via`
- [x] `medical_records`: `amendment_of_id`, `template_id`, `signed_at`, `signature_hash`, `ai_generated`, `ai_metadata`
- [x] Tabla nueva `app_settings` (key-value global de plataforma)
- [x] Tablas nuevas `tags` + `taggables` (sistema polimorfo de etiquetas)
- [x] Modelos `User/Clinic/Patient/Appointment/MedicalRecord` actualizados (fillable + casts)
- [x] Ruta `logout` POST nombrada agregada (necesaria para `layouts/admin.blade.php`)
- [x] Cero impacto en data viva. Tests 307/307 verde.

---

## � Fix crítico PDF/Livewire (2026-04-30) ✅

- [x] `TenantMiddleware` + `SetLocale` registrados como persistent middleware en `AppServiceProvider`
- [x] Todos los `exportPdf` en Livewire ahora funcionan correctamente (no más `Target class [current_clinic] does not exist`)
- [x] `Patients\Show::exportPdf` usa `$patient->clinic` como defense-in-depth
- [x] 29/29 tests PDF/Export/Print pasando

---

## �📱 Nav móvil — Fase A (2026-04-29) ✅ COMPLETADA

- [x] Drawer overlay slide-in desde la izquierda con backdrop oscuro (ya no empuja la página hacia abajo)
- [x] Bloqueo de scroll de body cuando el drawer está abierto (`x-effect` + `overflow-hidden`)
- [x] Íconos heroicons inline en cada item del drawer
- [x] Agrupación por secciones: **Operación** (Panel/Pacientes/Citas/Calendario), **Equipo** (Personal/Reportes), **Cuenta** (Configuración/Perfil/Facturación/Cerrar Sesión)
- [x] Active state reforzado: fondo índigo + ring + ícono coloreado
- [x] Botones del drawer con `aria-label`, `role="dialog"`, `aria-modal`, cierre con tecla Escape y tap en backdrop
- [x] Logout en móvil con tono rojo claro para diferenciarlo del resto
- [x] i18n nuevas: `nav_main`, `nav_team`, `nav_account`, `open_menu`, `close_menu`
- [x] Tests: `NavigationDrawerTest` (2 escenarios: rendering + permisos)
- [x] Suite completa: 306/306 verde · build Vite OK

---

## 🎯 Plan v1.0 Preliminar (lanzamiento mínimo funcional)

**Bloques bloqueantes para considerar la app lanzable:**

1. **Sprint Print/Export (CSV + PDF)** ✅ COMPLETADO
   - ✅ Sprint A — PDF de reportes pulido
   - ✅ Sprint B — Pacientes: CSV + PDF (listado + ficha individual)
   - ✅ Sprint C — Citas: CSV + PDF (agenda + comprobante individual)
   - ✅ Sprint D — Historiales: PDF consulta individual (SOAP, signos vitales, diagnósticos, prescripciones, firmas) · respeta confidencialidad
   - ✅ Sprint E — Staff: PDF directorio interno (requiere `users.print`)
   - ✅ Permisos `*.export` y `*.print` por módulo en seeder
2. **Fase 3C** — Permisos personalizados (UI toggles en Staff Edit) ✅ COMPLETADO
3. **Hardening producción** ✅ COMPLETADO
   - ✅ Paddle webhook secret (PADDLE_WEBHOOK_SECRET en .env.example, validado por Cashier)
   - ✅ Cron scheduler (`appointments:send-reminders` cada hora)
   - ✅ Rate limiters globales: `api`, `global`, `sensitive`, `webhook`
   - ✅ Policies por modelo: Patient, Appointment, MedicalRecord (tenant + permiso + confidencialidad)

Con esos 3 bloques, la app es **lanzable como v1.0 preliminar**. El resto (portal paciente, SMS/WhatsApp, telemedicina, IA, mobile, API) es roadmap v2+.

**Stack PDF elegido:** `barryvdh/laravel-dompdf` para documentos formateados (sin dependencia de Chrome). Para reportes con gráficas se mantiene `window.print()` + canvas→img.

---

## 🛡️ Hardening Fase 7/8 (2026-04-30) ✅ COMPLETADO

### Fase 7 — Profile + Ownership Transfer
- [x] **A1**: `transferOwnership` envuelto en `DB::transaction` + entrada explícita en Activity Log (`ownership_transferred`) con properties `previous_owner_id/name`, `new_owner_id/name`. Manejo de excepciones con `report()` + notificación de error.
- [x] **B1**: `locale` y `timezone` ahora son `<select>` (validación con `Rule::in`) con opción "Usar valor por defecto de la clínica".
- [x] **B2**: `updateProfile` emite evento DOM `profile-updated` (vía `$this->js()`) para que la navbar actualice el nombre en vivo.
- [x] **B3**: Cambio de email envía `sendEmailVerificationNotification()` al nuevo correo además de invalidar `email_verified_at`.

### Fase 8 — Reports
- [x] **A2**: Todos los métodos de gráfica usan `baseQuery()` unificado (`withoutGlobalScope('clinic')` + filtros) — consistencia con tests/multi-tenant.
- [x] **A3**: Labels de gráficas (`appointmentsByStatus`/`Type`) traducidas; el array `colors` se emite junto al JSON para que el JS mantenga consistencia visual.
- [x] **B4**: Export CSV incluye cabecera con clínica, generado_at, periodo y filtros aplicados (doctor/estado/tipo); valores traducidos; resumen final con totales.
- [x] **C3**: Botón "Limpiar filtros" visible cuando hay filtros activos (`clearFilters()` + i18n).

### Validación
- [x] 268/268 tests pasando
- [x] Pint clean
- [x] `npm run build` OK

---

## ✅ Completado

### Infraestructura Base
- [x] Proyecto Laravel 12 creado
- [x] TALL Stack configurado (Tailwind + Alpine + Livewire 3)
- [x] Laravel Breeze instalado (autenticación)
- [x] Base de datos MySQL configurada (controclinic)
- [x] Estructura de carpetas definida
- [x] Sistema de contexto (.context/) para AI
- [x] Storage link para uploads

### Multi-tenancy
- [x] Modelo `Clinic` con UUID (tenant principal)
- [x] Migración de clinics con planes y límites
- [x] `TenantMiddleware` para aislamiento (soporta string y Clinic object)
- [x] Route Model Binding para {clinic} por slug
- [x] Global Scopes por clinic_id

### Modelos Core
- [x] `Clinic` con settings JSON y branding JSON
- [x] `User` con roles y relación a clinic
- [x] `Patient` con UUID y datos médicos completos
- [x] `Appointment` con 3 modalidades de citas
- [x] `MedicalRecord` para historiales

### Roles y Permisos
- [x] Spatie Permission instalado
- [x] Roles: owner, doctor, assistant, receptionist
- [x] Permisos básicos creados
- [x] Seeder de roles y permisos

### Localización
- [x] Multi-idioma configurado (ES/EN)
- [x] Archivos de traducción: patients.php, settings.php, general.php, appointments.php
- [x] Selector de idioma en navegación (ES/EN con sesión)
- [x] Zonas horarias completas (Canadá, USA, México, LATAM, Europa)
- [x] Monedas completas (22 monedas soportadas)

### CRUD Pacientes (Livewire) ✅
- [x] `App\Livewire\App\Patients\Create` - Formulario completo
- [x] `App\Livewire\App\Patients\Edit` - Edición con validaciones
- [x] `App\Livewire\App\Patients\Show` - Detalle del paciente
- [x] Lista de pacientes con búsqueda y paginación
- [x] Vistas Blade para todos los componentes
- [x] Rutas configuradas con TenantMiddleware
- [x] Activity Log funcionando (soporta UUIDs)

### Módulo Settings Completo ✅
- [x] `App\Livewire\App\Settings\Index` - 6 tabs de configuración
- [x] Tab General: nombre, email, teléfono, dirección, país
- [x] Tab Localización: idioma, zona horaria, moneda, formato fecha/hora
- [x] Tab Citas: duración, buffer, días laborales, horarios
- [x] Tab Notificaciones: recordatorios, confirmaciones
- [x] Tab Facturación: datos fiscales
- [x] Tab Branding: logo, colores primario/secundario
- [x] Traducciones settings.php completas

### Sistema de Colores Dinámicos ✅
- [x] CSS Variables para colores de clínica
- [x] Conversión hex a RGB dinámica
- [x] Override de clases indigo-* con color primario
- [x] Clase `btn-primary` con color dinámico
- [x] Clase `bg-primary` disponible en Tailwind
- [x] Aplicado a botones, tabs, toggles

### Navegación ✅
- [x] Nav links funcionales: Dashboard, Pacientes, Citas, Calendario, Personal, Reportes
- [x] Icono de engranaje para Settings
- [x] Menú móvil responsive con Settings
- [x] Dropdown de usuario con logout

### Datos Demo
- [x] Clínica "Demo" creada
- [x] Usuario doctor@controclinic.com (owner)
- [x] Usuario asistente@controclinic.com (assistant)

---

### Sistema de Citas ✅
- [x] Modelo `Appointment` creado con 7 estados y 5 tipos
- [x] Migración ejecutada
- [x] `App\Livewire\App\Appointments\Index` - Lista con filtros y paginación
- [x] `App\Livewire\App\Appointments\Create` - Formulario con búsqueda de paciente
- [x] `App\Livewire\App\Appointments\Edit` - Edición con validación
- [x] `App\Livewire\App\Appointments\Show` - Detalle con timeline
- [x] Workflow de estados (confirmar, check-in, iniciar, completar, cancelar, no-show)
- [x] Validación de conflictos de horario (checkConflicts)
- [x] Traducciones appointments.php (ES/EN)
- [ ] Calendario visual (mejora futura)

### Dockerización ✅
- [x] Dockerfile (PHP 8.3-FPM + extensiones)
- [x] docker-compose.yml (app, nginx, mysql, redis, node, phpmyadmin, mailpit)
- [x] Nginx config + PHP config
- [x] Migración a WSL Ubuntu completada
- [x] Vite HMR configurado para Docker
- [x] phpMyAdmin en :8089
- [x] Mailpit en :8025 (SMTP testing)

### Dark/Light Mode ✅
- [x] Tailwind darkMode: 'class'
- [x] Toggle en navegación (desktop + mobile)
- [x] Persistencia en DB (campo 'theme' en users)
- [x] Anti-flash script en layouts
- [x] Traducciones ES/EN
- [x] Persistencia en navegación SPA (livewire:navigated listener en app.js)

### Sistema de Registro de Clínicas ✅
- [x] Formulario: nombre clínica, nombre owner, email, password
- [x] DB::transaction creando Clinic + User + Spatie Role
- [x] Slug único generado automáticamente
- [x] Plan Free por defecto con límites
- [x] Verificación de email (MustVerifyEmail)
- [x] Redirección a onboarding tras verificar
- [x] Traducciones auth.php (ES/EN)

### Onboarding Wizard (5 pasos) ✅
- [x] Componente Livewire multi-step (App\Livewire\App\Onboarding\Index)
- [x] Paso 1: Datos clínica (teléfono con código de área + banderitas, dirección, ciudad, país)
- [x] Paso 2: Localización (timezone, moneda, idioma)
- [x] Paso 3: Branding (colores primario/secundario con preview)
- [x] Paso 4: Horarios con jornada partida/continua, turnos mañana/tarde, sección fin de semana separada
- [x] Paso 5: Selección de plan (Free, Solo, Group, Enterprise) con tarjetas y features
- [x] Middleware EnsureOnboardingCompleted
- [x] Opción de saltar (skip)
- [x] Traducciones onboarding.php (ES/EN)
- [x] Selector de teléfono con 20 países LATAM/ES/US + banderas + códigos
- [x] Auto-sync phone_country al cambiar país
- [x] Planes pagos marcados como "Próximamente", guarda preferencia desired_plan

### Repositorio
- [x] GitHub: szystems/controclinic
- [x] Branch principal: main

### Páginas Públicas (Marketing) ✅
- [x] Layout público con nav, footer, SEO meta tags
- [x] Landing page: hero, features (6), cómo funciona, testimonios, CTA
- [x] Página de precios: 4 planes, toggle mensual/anual, tabla comparativa, FAQ, add-ons
- [x] Página de contacto: formulario + info soporte
- [x] Portal público de clínica: /public/{clinic}
- [x] Precios alineados: Free $0, Solo $29/$23, Group $79/$63, Enterprise personalizado
- [x] Rutas: /, /pricing, /contact

### Integración Paddle (Billing) ✅
- [x] Laravel Cashier Paddle configurado (env vars, config/cashier.php con price IDs)
- [x] Componente Livewire: App\Livewire\App\Billing\Index
  - Checkout con Paddle overlay (customData: clinic_id, plan, cycle)
  - Cambio de plan (swap)
  - Cancelar / reanudar suscripción
  - Portal de cliente Paddle (facturas, métodos de pago)
  - Toggle mensual/anual con -20%
- [x] Vista billing: plan actual, stats de uso, tarjetas de planes, historial transacciones
- [x] Middleware CheckPlanLimits: verifica suscripción activa, auto-downgrade a free
- [x] Event Listener: PaddleEventListener (SubscriptionCreated/Updated/Canceled)
  - Sincroniza plan_type y límites desde price_id del webhook
- [x] Rutas: app/{clinic}/billing (fuera de CheckPlanLimits, dentro de EnsureOnboarding)
- [x] Traducciones billing.php (ES/EN)
- [x] Configurar productos reales en Paddle Dashboard
- [x] Trial de 14 días en checkout
- [x] Tests de billing

### Límites por Plan ✅
- [x] Verificación de límite de pacientes en Patient Create
- [x] Verificación de límite de citas/mes en Appointment Create
- [x] Dashboard Livewire con barras de progreso de uso (pacientes, citas, doctores, staff)
- [x] Banners de upgrade: amarillo al 80%, rojo al 100%
- [x] Traducciones de límites ES/EN (general.php, appointments.php)
- [x] 26 tests de límites de plan (PlanLimitsTest.php)

### Paddle Sandbox Configurado ✅
- [x] Cuenta Paddle sandbox creada (Seller ID: 53650)
- [x] 2 productos: ControClinic Solo, ControClinic Group
- [x] 4 precios: Solo Mensual $29, Solo Anual $276, Group Mensual $79, Group Anual $756
- [x] Todas con trial de 14 días
- [x] Variables de entorno configuradas (.env + .env.docker)
- [x] Conexión API verificada (2 productos, 4 precios encontrados)

### Panel Admin SaaS ✅
- [x] Modelo `Plan` con migración (tabla plans + FK plan_id en clinics)
- [x] PlansSeeder: 4 planes (Free, Solo, Group, Enterprise) con updateOrCreate
- [x] Clinic model actualizado: plan() BelongsTo, getPlanLimits() usa Plan DB con fallback a constantes
- [x] Campo is_super_admin en users + EnsureIsAdmin middleware
- [x] Layout admin (layouts/admin.blade.php) con nav dark y badge ADMIN
- [x] Admin Dashboard: stats, clínicas por plan, planes overview, clínicas recientes
- [x] Admin Plans: listado con límites/precios/clinics count, edición completa con toggles unlimited
- [x] Admin Clinics: lista con búsqueda/filtros, detalle con stats/acciones (suspender/activar/extender trial/cambiar plan)
- [x] syncClinicLimits() al editar plan propaga cambios a todas las clínicas del plan
- [x] Billing/Index.php migrado a Plan model (ya no usa PLANS constant)
- [x] PaddleEventListener migrado a Plan model (resuelve plan desde Paddle price IDs en DB)
- [x] Traducciones admin.php (ES/EN)
- [x] 22 tests del admin panel (middleware, dashboard, plans CRUD, clinics management, Plan model)

### Admin Panel - Mejoras Adicionales ✅
- [x] Plan Manual (cortesías): is_manual_plan + manual_plan_reason en clinics
- [x] PaddleEventListener respeta is_manual_plan (no override)
- [x] Panel de suscripción Paddle en detalle de clínica (read-only)
- [x] Admin Dashboard mejorado: suscripciones activas, ingresos, tasa conversión, desglose suscripciones, transacciones recientes
- [x] Traducciones: dashboard stats, transacciones, suscripciones (ES/EN)
- [x] 127 tests (278 assertions) - todos pasando

### Upgrade Nudges (Conversión) ✅
- [x] Componente reutilizable `x-upgrade-nudge` (3 variantes: inline, button, banner)
- [x] Diferencia owner vs otros roles (owner ve Upgrade, otros ven "contacta al admin")
- [x] Dashboard header: botón "Upgrade" junto al badge del plan (free/solo, solo owner)
- [x] Dashboard Quick Actions: si al límite → botón bloqueado con nudge inline
- [x] Dashboard Usage: pacientes/citas restantes + "Desbloquear recursos ilimitados"
- [x] Pacientes Index: si !canAddPatient() → botón "Mejorar para continuar" reemplaza "Nuevo Paciente"
- [x] Citas Index: si !canAddAppointmentThisMonth() → nudge reemplaza "Nueva Cita"
- [x] Empty state de pacientes: nudge cuando al límite
- [x] Traducciones ES/EN: upgrade, limit_reached_contact_admin, patients/appointments_remaining, unlock_unlimited
- [x] Sin pop-ups ni modales intrusivos - diseño contextual y suave

---

## ❌ Pendiente Crítico (Infraestructura SaaS)

### Fase 1.1 - Landing Page ✅
- [x] Completado (ver sección Páginas Públicas arriba)

### Fase 1.5 - Integración Paddle ✅ (parcial)
- [x] Laravel Cashier Paddle configurado
- [x] Checkout, cambio de plan, cancelar/reanudar
- [x] Webhooks event listener
- [x] Middleware CheckPlanLimits
- [ ] Crear productos reales en Paddle Dashboard
- [ ] Trial de 14 días en checkout

### Fase 2 - Panel Admin SaaS ✅
- [x] Dashboard de métricas (clínicas totales, activas, trial, usuarios)
- [x] Gestión de clínicas (lista, búsqueda, filtros, detalle, suspender/activar/cambiar plan)
- [x] Gestión de planes en DB (CRUD, editar límites, sincronización a clínicas)
- [x] Billing y PaddleEventListener migrados a Plan model (DB-driven)

### Fase 3 - Límites por Plan ✅
- [x] Middleware CheckPlanLimits
- [x] Límites: usuarios, pacientes, citas
- [x] Upgrade prompts

### Fase 2.5 - Tiers Consistentes ✅
- [x] Componentes x-plan-card y x-plan-comparison dinámicos desde Plan model (DB)
- [x] Refactorizar /pricing (hardcodeado → dinámico)
- [x] Refactorizar Onboarding paso 5 (hardcodeado → dinámico)
- [x] Refactorizar Billing page (arrays mapeados → Plan models directos)
- [x] Features traducidas desde keys JSON del Plan model (features.php ES/EN)
- [x] Plan model con métodos display (translated_description, display_features, etc.)

### Fase 3 - Gestión de Equipo

#### Fase 3A — CRUD de Equipo ✅
- [x] Rutas: `/app/{clinic}/staff` (index, create, /{user}/edit)
- [x] `App\Livewire\App\Staff\Index` — lista con búsqueda, filtros (rol, estado), sort, paginación
- [x] `App\Livewire\App\Staff\Create` — formulario con validación de límites de plan
- [x] `App\Livewire\App\Staff\Edit` — edición con contraseña opcional, cambio de rol con validación
- [x] Activar/Desactivar miembros (toggle is_active)
- [x] Eliminar miembros (soft delete) con verificaciones (no owner, no self)
- [x] Solo owners pueden gestionar equipo (`users.manage` permission)
- [x] Enlace en navbar (desktop + mobile) con `@can('users.manage')` guard
- [x] Badges de uso: "Doctores: X/Y" y "Personal: X/Y" con nudges
- [x] Upgrade nudges cuando se alcanzan límites
- [x] Traducciones staff.php (ES/EN, ~95 keys)
- [x] 25 tests (53 assertions) — rendering, CRUD, límites, permisos, usage badges
- [x] 152 tests totales (331 assertions) — sin regresiones

#### Fase 3B — Invitaciones por Email ✅
- [x] Modelo ClinicInvitation (UUID, token único, expiración 7 días)
- [x] Migración: clinic_invitations con clinic_id, email, name, role, token, invited_by, expires_at, accepted_at, cancelled_at
- [x] Mailable ClinicInvitationMail con template markdown
- [x] Flujo: owner invita → email enviado → usuario acepta con contraseña → cuenta creada
- [x] Staff\Create modificado: envía invitación en vez de crear usuario directo
- [x] Staff\Index: sección de invitaciones pendientes con reenviar/cancelar
- [x] Ruta pública /invitation/{token} para aceptar (sin auth)
- [x] Componente Livewire Accept con layout guest
- [x] Validaciones: email duplicado, invitación duplicada, límites de plan, expiración, token inválido
- [x] Traducciones invitations.php (ES/EN, ~35 keys)
- [x] Relaciones en Clinic: invitations(), pendingInvitations()
- [x] 23 tests de invitaciones (61 assertions)
- [x] 175 tests totales (391 assertions) — sin regresiones

### Portal Público de Clínica ✅ (2026-04-28)
- [x] Ruta `/c/{slug}` y alias `/public/{slug}` con route binding por slug o public_portal_slug
- [x] `App\Livewire\Public\Booking` — wizard de 3 pasos (doctor → fecha/hora → datos paciente)
- [x] Layout `components/layouts/public-clinic.blade.php` con branding dinámico (CSS vars)
- [x] Aborta 404 si `public_portal_enabled = false`
- [x] Slots calculados con `working_hours`, `appointment_duration`, días laborales
- [x] Detección de conflictos de horario por doctor (whereDate + start_time)
- [x] Honeypot anti-spam + RateLimiter (5/min por IP+clinic)
- [x] Reutiliza paciente por email o teléfono dentro de la clínica
- [x] Estado inicial según `require_booking_confirmation` (scheduled / confirmed)
- [x] Pantalla de confirmación con número de referencia (8 chars del UUID)
- [x] Selector ES/EN en header del portal
- [x] Traducciones booking.php (ES/EN, ~50 keys)
- [x] 14 tests de booking público

### Sistema de Notificaciones por Email ✅ (2026-04-28)
- [x] 5 Mailables: AppointmentBookedToPatient, AppointmentBookedToClinic, AppointmentConfirmed, AppointmentCancelled, AppointmentReminder
- [x] 5 templates Markdown en `resources/views/mail/appointments/`
- [x] Job `SendAppointmentNotification` (ShouldQueue, 3 reintentos, switch por tipo)
- [x] Locale por clínica (`Mail::to()->locale($clinic->locale)`)
- [x] Disparadores automáticos:
  - Booking público → al paciente (si tiene email) + a la clínica
  - `Appointment::confirm()` → al paciente
  - `Appointment::cancel()` → al paciente
- [x] Comando `appointments:send-reminders --hours=24 --dry-run` con scheduler horario
- [x] Marca `reminder_sent=true` para evitar duplicados
- [x] Traducciones appointments_mail.php (ES/EN)
- [x] 10 tests del módulo
- [x] Worker `queue:work` configurado para entorno Docker
- [x] Verificado en Mailpit con locale ES correcto

### Actualización de Dependencias ✅ (2026-04-28)
- [x] Composer minor/patch: Breeze 2.4.1, Cashier-Paddle 2.8.1, Pail 1.2.6, Pint 1.29.1, Sail 1.58, Volt 1.10.5, Localization 2.4, Collision 8.9.4, Activitylog 4.12.3
- [x] Parches de seguridad: commonmark 2.8.2 (CVE-2026-33347), psysh 0.12.22 (CVE-2026-25129)
- [x] npm: @tailwindcss/vite 4.2.4, autoprefixer 10.5.0, axios 1.15.2, postcss 8.5.12, vite 7.3.2
- [x] picomatch (ReDoS) y rollup (path traversal) parcheados vía npm audit fix

### Sprint Estabilización ✅ (2026-04-28)
**Bloque A — Documentación:** PROJECT/MODELS/ROADMAP/STATUS/CONVENTIONS al día · ADR-008/009/010 añadidos · Sprint insertado en TASKS.

**Bloque B — Seguridad:**
- [x] Rate limiting: login (10/min), register/forgot-password/reset-password (5/min), invitation accept (10/min)
- [x] `BelongsToClinic` aplicado a Patient, Appointment, MedicalRecord (con guard `app()->bound('current_clinic')` para rutas públicas)
- [x] **Cross-tenant data leak FIX**: `abort_if($model->clinic_id !== current->id, 404)` en Patients/Show, Patients/Edit, Staff/Edit (defense-in-depth porque SubstituteBindings corre antes que TenantMiddleware)
- [x] `MultiTenantIsolationTest` con 10 tests cubriendo cross-tenant access, list filtering, super-admin, factories
- [x] Defense-in-depth documentado en CONVENTIONS.md

**Bloque C — Bugs y polish:**
- [x] **Timezone fix** en `SendAppointmentReminders`: comparación en `clinic.timezone` (Carbon parse con tz, no setTimezone) + test específico con Asia/Tokyo
- [x] Demo seeder: `trial_ends_at = now()->addDays(30)` + 8 pacientes + 16 citas (8 completadas + 8 confirmadas)
- [x] Páginas de error custom: 403/404/419/500/503 con layout compartido y traducciones ES/EN (`lang/*/errors.php`)
- [x] Páginas legales: `/terms` y `/privacy` con vistas Blade y traducciones ES/EN (`lang/*/legal.php`)
- [x] `Mail::to()->locale($clinic->locale)` en `Staff\Create` y `Staff\Index::resendInvitation`

**Bloque D — DX/CI:**
- [x] Composer scripts: `lint`, `format`, `test`, `stan`, `check` (orquesta todo)
- [x] GitHub Actions: `.github/workflows/ci.yml` con 3 jobs (tests + Pint + PHPStan) en PHP 8.3
- [x] Larastan v3.9 + `phpstan.neon` level 5 + `phpstan-baseline.neon` (116 errores legacy congelados, 0 nuevos permitidos)
- [x] README.md: sección "Quick Start (Docker)" + tabla servicios + comandos diarios

**Métricas finales:** 212 tests (3 nuevos: timezone, terms, privacy) · 464 asserts · Pint 100% clean · PHPStan level 5 sin errores nuevos.

#### Fase 3C — Permisos por Usuario (Baja - Final del proyecto)
- [ ] UI para que el owner personalice permisos individuales por usuario
- [ ] Toggles por permiso agrupados por módulo
- [ ] Se expande conforme se creen nuevos módulos
- [ ] No implementar hasta tener todos los módulos definidos

#### Fase 3D — Extras Multi-usuario (Baja)
- [ ] Perfil propio del staff, reset de contraseña, transferir ownership
- [ ] Historial de actividad por usuario

---

## 📊 Métricas del Proyecto

```
Modelos creados: 7 (Clinic, User, Patient, Appointment, MedicalRecord, Plan, ClinicInvitation)
Migraciones: 24+
Componentes Livewire: 23 (incluye Public\Booking)
Mailables: 6 (ClinicInvitation + 5 de citas)
Jobs: 1 (SendAppointmentNotification)
Comandos Artisan: 1 (appointments:send-reminders)
Vistas Blade: ~70
Archivos de traducción: 24 (ES/EN x 12 módulos)
Rutas definidas: 46+
Tests: 199 (445 assertions) — todos pasando
Repositorio: github.com/szystems/controclinic
```
- [x] Tests de multi-tenancy (TenantMiddlewareTest)

---

## 🚀 Roadmap

Ver [ROADMAP.md](ROADMAP.md) para visión por trimestres.
Ver [TASKS.md](TASKS.md) para tareas concretas priorizadas.

---

## 🐛 Issues Conocidos

1. **Webhook Paddle** — Sin webhook secret en local (necesita URL pública para verificar firmas).
2. **Assets** — Recompilar con `npm run build` tras cambios; eliminar `public/hot` si Vite dev no está corriendo.
3. **Calendario visual** — La ruta `calendar` apunta a `AppointmentsIndex` (placeholder hasta Fase 6).
4. **Política de acceso** — Cuando `trial_ends_at` expira el `TenantMiddleware` aborta con 403. Pendiente modo READ-ONLY (Fase 4, ver `DECISIONS.md` ADR-008).
5. **Scheduler en producción** — Necesita `php artisan schedule:run` cada minuto (cron) para enviar recordatorios.
6. **Recordatorios y zonas horarias** — `SendAppointmentReminders` compara en hora del servidor; debería operar en `clinic.timezone`.
