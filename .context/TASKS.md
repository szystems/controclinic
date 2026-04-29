# 📝 Tareas Pendientes

> Actualizado: 2026-04-29
> Enfoque: SaaS-First

---

## 🔥 Prioridad Alta — PRÓXIMO SPRINT

> Orden lógico: primero **estabilizar la base** (docs, seguridad, bugs latentes, DX), luego cerrar el ciclo de vida de la cuenta (Fase 4), después completar el flujo médico (historias clínicas), después UX (calendario, perfil) y finalmente personalización avanzada.

---

### 🛡️ Sprint Estabilización 🔥 ANTES DE TODO
> **Por qué primero:** consolidar documentación, seguridad, bugs latentes y DX para que las próximas fases tengan una base limpia, testeada y observable. Sin esto, cada feature nueva acumula deuda técnica.

#### Bloque A — Documentación y ADRs ✅
- [x] Actualizar `PROJECT.md` (versión, puerto 8088, DB real, precios reales)
- [x] Regenerar `MODELS.md` (agregar Plan, ClinicInvitation, campos faltantes)
- [x] Reorientar `ROADMAP.md` (eliminar fases ya completadas, redirigir a TASKS.md)
- [x] Limpiar `STATUS.md` (eliminar "Próximas Fases" obsoleta)
- [x] Añadir ADR-008 (Política de Acceso), ADR-009 (Notificaciones), ADR-010 (Plan Free cortesía)
- [x] Verificar/actualizar `CONVENTIONS.md` (BelongsToClinic, ActivityLog, Tests)
- [x] Insertar Sprint Estabilización en este archivo (TASKS.md)

#### Bloque B — Seguridad + Multi-tenant tests ✅
- [x] Rate limiting en rutas sensibles: `/login`, `/register`, `/forgot-password`, `/invitation/*`
- [x] `tests/Feature/MultiTenantIsolationTest.php` con 2 clínicas + cross-data leak verificación (10 tests)
- [x] **CRÍTICO**: fix cross-tenant leak en Patients/Show, Patients/Edit, Staff/Edit (abort_if defense-in-depth)
- [x] `BelongsToClinic` aplicado a Patient, Appointment, MedicalRecord (con guard `app()->bound`)
- [x] Auditar factories: `clinic_id` siempre real (verificado en MultiTenantIsolationTest)

#### Bloque C — Bugs y polish ✅
- [x] **Timezone fix** en `SendAppointmentReminders` (compara en `clinic.timezone` con test específico)
- [x] Demo seeder: `trial_ends_at = now()->addDays(30)` + 8 pacientes + 16 citas demo
- [x] Páginas de error custom: `403`, `404`, `500`, `419`, `503` con branding y traducciones ES/EN
- [x] Páginas legales: `/terms`, `/privacy` con vistas Blade y traducciones ES/EN
- [x] `Mail::to()->locale($clinic->locale)` en Staff\Create y Staff\Index (resend)

#### Bloque D — Developer Experience / CI ✅
- [x] Scripts en `composer.json`: `lint`, `format`, `test`, `stan`, `check`
- [x] GitHub Actions workflow: `.github/workflows/ci.yml` (tests PHP 8.3 + Pint + PHPStan)
- [x] Larastan/PHPStan nivel 5 con baseline (116 errores legacy congelados, 0 nuevos)
- [x] README.md con quick-start Docker + tabla de servicios + comandos diarios
- [ ] Telescope en local (opcional, pospuesto)

**Estado final del Sprint:** 212 tests / 464 asserts ✓ · Pint clean ✓ · PHPStan clean ✓

---

### Fase 4 — Política de Acceso (Trial Expirado / Read-Only) ✅ COMPLETADA (2026-04-28)
> Implementación de ADR-008 (full / read_only / billing_only).

#### 4.1 Modelo & lógica core ✅
- [x] `Clinic::accessLevel()` → `full | read_only | billing_only`
- [x] `Clinic::isAccessible() / canWrite() / isReadOnly() / isBillingOnly()`
- [x] Constantes `ACCESS_FULL`, `ACCESS_READ_ONLY`, `ACCESS_BILLING_ONLY`
- [x] `canAddPatient/Appointment/Doctor/Staff` short-circuit con `!canWrite()`

#### 4.2 Middlewares ✅
- [x] `TenantMiddleware`: redirige a billing si `!isAccessible()` (sólo 403 si user no pertenece)
- [x] `EnsureCanWrite` middleware (alias `can.write`) aplicado a Create/Edit/Settings
- [x] `CheckPlanLimits` refactor: silent downgrade only (no más redirects)

#### 4.3 UI / Experiencia ✅
- [x] `<x-account-status-banner>` global ámbar/rojo en layout app
- [x] `<x-upgrade-nudge>` con tooltips contextuales (límite vs read-only)
- [x] Plan badge color refleja accessLevel (verde/ámbar)
- [x] Free cortesía (`is_manual_plan=true`) sin nags ni banners
- [x] Portal público `/c/{slug}` muestra "Reservas no disponibles" si `!canWrite()`

#### 4.4 Tests ✅
- [x] `ClinicAccessLevelTest` (7 estados unitarios)
- [x] `EnsureCanWriteTest` (6 escenarios feature)
- [x] `PublicBookingAccessLevelTest` (3 escenarios)
- [x] Adjustes en `CheckPlanLimitsTest` para nuevo flujo

**Estado final Fase 4:** 227 tests / 511 asserts ✓ · Pint clean ✓ · PHPStan clean ✓
**Commits:** `e95386f` + `b56b556` + `5cbb98a`

---

### Fase 5 — Historial Médico (MedicalRecord CRUD) ✅ COMPLETADA
> Cierre Fase 5: 237 tests / 538 asserts — Pint OK — PHPStan OK.

#### 5.1 Backend
- [x] Modelo `MedicalRecord` revisado (campos SOAP, vital_signs, diagnoses, prescriptions, soft deletes, activity log).
- [x] `MedicalRecordFactory` con states (`draft`, `consultation`, `prescription`, `withVitalSigns`, `confidential`, `forPatient`, `forAppointment`).
- [x] Permisos Spatie ya seedeados (`records.view/create/edit/delete/view_confidential`).
- [x] Rutas anidadas `patients/{patient}/records/*` con write protegidas por `can.write`.

#### 5.2 Livewire + UI
- [x] `App\Livewire\App\MedicalRecords\Index` (filtros tipo/estado, oculta confidenciales sin permiso, paginación).
- [x] `App\Livewire\App\MedicalRecords\Show` (tenant guard triple, bloqueo confidencial, delete con permiso).
- [x] `App\Livewire\App\MedicalRecords\Create` (SOAP completo, vital signs, repeaters diagnóstico/prescripción, draft vs final).
- [x] `App\Livewire\App\MedicalRecords\Edit` (sólo borradores, redirige a show si está finalizado).
- [x] Vistas Blade con dark mode + traducciones ES/EN (`lang/{es,en}/records.php`).

#### 5.3 Integración
- [x] Botón "Nueva consulta" en `appointments/show` con `?appointment_id=` (sólo si `canWrite()`).
- [x] Sección "Recent Records" en `patients/show` enlaza al historial completo y al show individual.
- [x] Pre-fill automático de tipo/título cuando se crea desde una cita.

#### 5.4 Tests (10 nuevos, todos verdes)
- [x] Index renderiza con permiso, prohíbe sin permiso.
- [x] Create persiste como draft y como final (con vital signs, diagnósticos, prescripciones).
- [x] Show bloquea acceso cross-tenant y oculta confidenciales sin `view_confidential`.
- [x] Edit sólo permitido en borradores (finalizados → redirect a show).
- [x] Create/Edit bloqueados por `can.write` cuando la cuenta está read-only.
- [x] Pre-fill desde appointment query param.
- [x] Delete requiere permiso `records.delete`.

---

### Fase 5 — Historial Médico (MedicalRecord CRUD) — Plan detallado (referencia histórica)

---

### 🔮 Fase futura: Flujo de enmienda formal de consultas
> **Estado:** Diferido. El status `STATUS_AMENDED` ya existe en el modelo pero no se usa.
> **Por qué se difirió:** Fase 5 priorizó CRUD básico. La enmienda es una funcionalidad médico-legal específica que merece diseño propio.
>
> **Lo que falta para activarlo:**
> - [ ] Migración: agregar columna `amendment_of_id` (FK a `medical_records.id`, nullable) en `medical_records`.
> - [ ] Modelo `MedicalRecord`: relación `amendmentOf()` y `amendments()` (hasMany inversa).
> - [ ] UI: en `medical-records/show.blade.php` (consulta finalizada) → botón **"Crear enmienda"** que abra Create con datos pre-rellenados de la original y `amendment_of_id` seteado.
> - [ ] `App\Livewire\App\MedicalRecords\Create`: aceptar parámetro `?amendment_of=` desde query string. Si está presente, marcar `status = STATUS_AMENDED` automáticamente al guardar y bloquear cambios al campo `record_type`.
> - [ ] UI: en `show.blade.php` mostrar bloque "Enmiendas posteriores" listando `$record->amendments` con link.
> - [ ] UI: en `index.blade.php` re-añadir `STATUS_AMENDED` al filtro (`recordStatuses()` en `App\Livewire\App\MedicalRecords\Index`).
> - [ ] Badges visuales distintos para `amended` (ej. lila) y referencia visible al record original.
> - [ ] Permiso nuevo `records.amend` (asignar a doctor + owner).
> - [ ] Tests Feature: enmienda crea registro nuevo sin tocar original, link bidireccional, sólo permitido sobre `final`.
>
> **Archivo afectado actual:** `app/Livewire/App/MedicalRecords/Index.php` línea ~88 (filtro `STATUS_AMENDED` removido temporalmente con comentario).

---

### Fase 6 — Calendario Visual de Citas (UX) ✅ COMPLETADA
> FullCalendar v6 + Livewire. 246 tests / 558 asserts.

- [x] Componente Livewire `App\Livewire\App\Appointments\Calendar`
- [x] Vista mensual / semanal / diaria / lista (toggle nativo de FullCalendar)
- [x] Drag & drop para reagendar (eventDrop → `rescheduleEvent` con guard read-only + permiso)
- [x] Filtros por doctor (color por doctor, chips toggle, hash estable)
- [x] Click en hueco vacío → redirige a Create con `?date=YYYY-MM-DD&time=HH:MM`
- [x] Click en cita → navega a Show vía `wire:navigate`
- [x] Reemplazada la ruta placeholder `app.appointments.calendar`
- [x] Tests Feature (9 tests: render, fetchEvents, multi-tenant, filtros, drag&drop, read-only, permisos)
- [x] Locale español/inglés (FullCalendar locales + traducciones propias)
- [x] Dark mode CSS para FullCalendar

---

### Fase 7 — Perfil del Usuario + Transferencia de Ownership (Fase 3D) ✅ COMPLETADA (2026-04-29)
- [x] Página `/app/{clinic}/profile` editable por cada usuario
- [x] Cambio de contraseña propia
- [x] Owner puede forzar reset de contraseña a un staff (con caja ámbar explicativa + manejo de errores SMTP)
- [x] Transferir ownership a otro usuario (confirmación Alpine.js 2 pasos, solo a doctores)
- [x] Historial de actividad por usuario (filtrar Activity Log por user_id)
- [x] Owner cuenta como practitioner en límites del plan (dashboard, billing, staff)
- [x] Seeder de roles/permisos idempotente (firstOrCreate)
- [x] Tests: 254 tests / 575 asserts ✓

**Estado final Fase 7:** 254 tests / 575 asserts ✓ · Pint clean ✓ · PHPStan clean ✓

---

### Fase 8 — Reportes / Dashboard Avanzado ✅ COMPLETADA (2026-04-29)
- [x] Reporte de citas por período (filtros: doctor, estado, tipo)
- [x] Reporte de pacientes nuevos por mes
- [x] Exportación a CSV (con BOM para Excel)
- [x] Gráficas interactivas (Chart.js 4.5.1): citas por día, por estado, por tipo, pacientes por mes
- [x] Permisos: `reports.view` (owner/doctor/admin), `reports.export` (owner/admin)
- [x] Nav link condicional `@can('reports.view')` en desktop + responsive
- [x] Traducciones ES/EN (`lang/{es,en}/reports.php`)
- [x] 14 tests Feature: acceso, aislamiento tenant, filtros, export CSV, períodos
- [x] 268 tests / 595 asserts ✓

**Estado final Fase 8:** 268 tests / 595 asserts ✓ · Pint clean ✓ · PHPStan clean ✓

---

### Fase 3C — Permisos Personalizados (Última)
> **Por qué al final:** depende de tener todos los módulos definidos para saber qué permisos personalizar.

- [ ] UI en `Staff\Edit` con tabla de permisos agrupados por módulo (Pacientes, Citas, Historiales, Reportes, Config)
- [ ] Toggle on/off por permiso por usuario (Spatie direct permissions)
- [ ] Botón "Restaurar permisos del rol"
- [ ] Preview de capacidades del usuario
- [ ] Activity Log de cambios de permisos
- [ ] Traducciones permissions.php
- [ ] Tests

---

## 🟢 Backlog (Sin orden estricto)

### Mejoras técnicas
- [ ] Rate limiting global en rutas públicas
- [ ] Policies de autorización por modelo (complementar Spatie)
- [ ] PHPDoc en métodos públicos
- [ ] CI/CD básico (GitHub Actions: tests + Pint)
- [ ] Webhook Paddle con secret en producción
- [ ] Cron de scheduler en producción

### Features futuras (Roadmap fase 2+)
- [ ] Portal del paciente (login, ver historial, próximas citas)
- [ ] Notificaciones SMS / WhatsApp Business
- [ ] Telemedicina (videollamada integrada)
- [ ] Importación de pacientes desde CSV/Excel
- [ ] Recetas electrónicas con QR
- [ ] IA: resúmenes de consulta, recordatorios inteligentes
- [ ] Mobile app
- [ ] API pública

---

## ✅ Completado Recientemente

### 2026-04-28 — Sistema de Notificaciones por Email
- 5 Mailables (booked-patient, booked-clinic, confirmed, cancelled, reminder)
- Job `SendAppointmentNotification` con cola Redis y locale por clínica
- Comando `appointments:send-reminders` + scheduler horario
- 10 tests, verificado en Mailpit

### 2026-04-28 — Portal Público de Clínica
- Ruta `/c/{slug}` con wizard de 3 pasos (doctor → fecha → paciente)
- Layout con branding dinámico (CSS vars de la clínica)
- Honeypot + RateLimiter, reutilización de paciente, conflictos de horario
- Selector ES/EN, traducciones booking.php, 14 tests

### 2026-04-28 — Actualización de dependencias
- Composer minor/patch + parches CVE (commonmark, psysh)
- npm: vite 7.3.2, tailwindcss/vite 4.2.4, etc.
- 199 tests pasando sin regresiones



---

## 📝 Notas

- **Convención:** mantener este archivo enfocado en lo PRÓXIMO. Lo viejo va a STATUS.md.
- **Decisiones arquitectónicas:** documentar en `.context/DECISIONS.md`.
- **Cambios mayores:** actualizar `STATUS.md` con la fecha y los tests que pasan.
- **Tests primero** en cada fase: definir los tests antes de implementar.

- [ ] Implementar sidebar colapsable para la navegación principal, inspirado en el diseño y experiencia del proyecto szystems (`/home/szott/proyectos/szystems`).
    - Sidebar debe permitir acceso directo a todos los módulos principales (Dashboard, Pacientes, Citas, Calendario visual, Staff, Reportes, Configuración, etc).
    - Debe ser colapsable (iconos solo) y expandible (iconos + texto).
    - Incluir agrupación de módulos secundarios (ej: "Módulos" con submenú).
    - Soporte para dark mode y responsivo (mobile/desktop).
    - Acceso rápido a perfil, ayuda y logout.
    - Tomar como referencia la UX/UI de las capturas y el proyecto szystems para lograr una experiencia moderna y eficiente.
    - No desarrollar aún, solo dejar planeado y priorizar después de la tarea de owner=doctor en plan solo.
