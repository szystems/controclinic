# 📝 Tareas Pendientes

> Actualizado: 2026-04-28
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

### Fase 4 — Política de Acceso (Trial Expirado / Read-Only) 🔥 DESPUÉS DEL SPRINT
> **Por qué después de Estabilización:** ya está documentada como ADR-008. Implementarla con la base limpia y los tests multi-tenant en su lugar.

#### 4.1 Modelo & lógica core
- [ ] Añadir a `Clinic`:
  - `accessLevel(): string` → `'full' | 'read_only' | 'billing_only' | 'blocked'`
  - `isAccessible(): bool` (todo excepto `cancelled`)
  - `isReadOnly(): bool` (trial expirado o suscripción inactiva con plan free)
  - `canWrite(): bool` (inverso de read-only)
- [ ] Mantener `isActive()` retrocompatible o reemplazar usos.

#### 4.2 Middlewares
- [ ] Modificar `TenantMiddleware`: 403 sólo para `cancelled` o si el usuario no pertenece. El resto pasa.
- [ ] Crear `EnsureCanWrite` middleware: si `isReadOnly()` y la ruta es de escritura → redirige a `app.billing.index` con flash.
- [ ] Modificar `CheckPlanLimits`: trial expirado en plan free → marca read-only y deja entrar al panel pero sólo a billing en vista de escritura.
- [ ] Aplicar `EnsureCanWrite` a rutas Create/Edit de patients, appointments, staff, settings (en grupo).

#### 4.3 UI / Experiencia
- [ ] Componente Blade `x-account-status-banner` (persistente arriba del layout app):
  - "Tu trial expiró el {fecha}. Actualiza tu plan para seguir creando registros."
  - Botón "Ver planes" → billing.
- [ ] Deshabilitar botones "Nuevo paciente / Nueva cita / Invitar staff" cuando read-only (visualmente atenuados con tooltip).
- [ ] En el portal público `/c/{slug}`: si `isReadOnly()` → mostrar mensaje "Reservas temporalmente no disponibles" en lugar del wizard (el dueño no podría atender).

#### 4.4 Tests
- [ ] Test cada estado: trial vigente, trial expirado, suspended, cancelled, plan pago activo, plan pago expirado.
- [ ] Test que `EnsureCanWrite` redirige bien en cada Livewire de escritura.
- [ ] Test del banner visible y portal público bloqueado en read-only.

---

### Fase 5 — Historial Médico (MedicalRecord CRUD) 🔥 SEGUNDA
> **Por qué después:** es el siguiente módulo médico crítico (la app guarda pacientes y citas pero aún no consultas). Depende de tener el ciclo de cuenta estable.

- [ ] Migración + modelo `MedicalRecord` (revisar lo existente, completar campos)
- [ ] `App\Livewire\App\MedicalRecords\Index` (listado por paciente)
- [ ] `App\Livewire\App\MedicalRecords\Create` (con plantilla SOAP: Subjetivo/Objetivo/Análisis/Plan)
- [ ] `App\Livewire\App\MedicalRecords\Edit`
- [ ] `App\Livewire\App\MedicalRecords\Show` (read-only para roles sin permisos de edición)
- [ ] Asociar al `Appointment` (botón "Crear consulta" desde Show del appointment)
- [ ] Permisos Spatie: `records.view`, `records.create`, `records.edit`, `records.delete`
- [ ] Adjuntos: subir documentos/imágenes (storage local)
- [ ] Activity Log + soft deletes
- [ ] Traducciones records.php (ES/EN)
- [ ] Tests Feature (CRUD + permisos + multi-tenant scope)

---

### Fase 6 — Calendario Visual de Citas (UX)
> **Por qué después:** la lista de citas funciona, el calendario es visual y de productividad. Requiere data ya estable.

- [ ] Componente Livewire `App\Livewire\App\Appointments\Calendar`
- [ ] Vista mensual / semanal / diaria (toggle)
- [ ] Drag & drop para reagendar (Alpine + emitir a backend)
- [ ] Filtros por doctor (color por doctor)
- [ ] Click en hueco vacío → modal con `Create`
- [ ] Click en cita → modal con `Show`
- [ ] Reemplazar la ruta placeholder `app.appointments.calendar`
- [ ] Tests

---

### Fase 7 — Perfil del Usuario + Transferencia de Ownership (Fase 3D)
- [ ] Página `/app/{clinic}/profile` editable por cada usuario
- [ ] Cambio de contraseña propia
- [ ] Owner puede forzar reset de contraseña a un staff
- [ ] Transferir ownership a otro usuario (con confirmación + email)
- [ ] Historial de actividad por usuario (filtrar Activity Log por user_id)
- [ ] Tests

---

### Fase 8 — Reportes / Dashboard Avanzado
- [ ] Reporte de citas por período (filtros: doctor, estado, tipo)
- [ ] Reporte de pacientes nuevos por mes
- [ ] Reporte de ingresos (cuando exista facturación de servicios)
- [ ] Exportación a CSV / PDF
- [ ] Gráficas en dashboard del owner (Chart.js o ApexCharts)
- [ ] Permisos: `reports.view`, `reports.export`
- [ ] Tests

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
