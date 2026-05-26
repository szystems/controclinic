# 📝 Tareas Pendientes

> Actualizado: 2026-05-25
> Estado: 567 tests / 1235 asserts · Pint clean · Sprint G en curso
> Sprints completados: A ✅ B ✅ C ✅ F.1→F.10 ✅

---

## 🚀 Sprint G — Pulido, Pagos y Admin (EN CURSO — 2026-05-25)

> **Objetivo:** Cerrar huecos visibles antes de lanzamiento: fixes de branding público,
> pagos parciales completos y panel admin con métricas reales.

### G.1 — Fixes de branding en páginas públicas y auth
> Logo aparece redundante en nav público. Logo no aparece en vistas de auth (login, register, etc).

- [ ] **Nav público** (`layouts/public.blade.php`): si hay logo configurado, mostrar solo imagen sin el texto `$appName` al lado
- [ ] **Auth views** (`layouts/guest.blade.php`): reemplazar `<x-application-logo>` (SVG hardcoded) por `<x-app-logo>` que ya lee `branding.logo_url`
- [ ] Verificar en: login, register, forgot-password, reset-password, verify-email

### G.2 — Completar pagos parciales en Facturación
> Tabla `invoice_payments` y UI en `invoices/show` ya existen. Revisar y completar si falta algo.

- [ ] Revisar `Livewire\App\Invoices\Show` — confirmar `openPaymentModal()`, `savePayment()`, `deletePayment()` implementados
- [ ] Confirmar que saldo pendiente (`amount_due`) se calcula como `total - sum(payments)`
- [ ] Confirmar que status cambia a `paid` automáticamente cuando `amount_due = 0`
- [ ] Verificar protección multi-tenant en `invoice_payments`
- [ ] Tests Feature: `InvoicePartialPaymentsTest`

### G.3 — Panel Admin: métricas de negocio
> Dashboard admin existe (Clinics/Index, Clinics/Show, Plans, Settings) pero sin métricas de negocio.

- [ ] KPIs en Dashboard Admin: total clínicas, activas este mes, free vs paid por plan, nuevas últimos 30 días
- [ ] Gráfica de registros de clínicas por semana (últimos 3 meses)
- [ ] Tabla de clínicas recientes: plan, estado, fecha registro, último login del owner
- [ ] i18n ES/EN en `lang/{es,en}/admin.php`

### G.4 — Tests Feature pendientes de Sprint F
> F.1 y F.2 quedaron marcados como pendientes de tests.

- [ ] `SetupChecklistTest` — visible solo para owner, pasos se marcan, dismiss, auto-dismiss al completar todos
- [ ] `EmptyStatesTest` — empty state aparece cuando no hay datos en cada módulo principal

### G.5 — Revisar módulo Recetas
> El módulo existe (está en nav). Determinar si está completo para v1 o necesita mejoras.

- [ ] Revisar componentes actuales: Index, Show, Create, Edit
- [ ] Verificar permisos correctos (`prescriptions.create`, etc.)
- [ ] Identificar si falta algo crítico para una receta médica básica (campos, PDF, firma)
- [ ] Decidir: ¿Sprint G o diferir?

---

## 🔜 Backlog — Ordenado por prioridad

### B.1 — Paddle checkout en onboarding (BLOQUEADO — esperando business number)
> Sin cuenta Paddle activa no se puede implementar.

- [ ] Integrar Paddle JS con price IDs de los 4 planes en paso 5 del onboarding
- [ ] Trial expiry emails automáticos: 7 días antes, 3 días antes, día de vencimiento

### B.2 — Métricas de conversión con Paddle
> Requiere cuenta Paddle activa para datos de ingresos reales.

- [ ] Free → paid conversion rate en Admin Dashboard
- [ ] MRR, ARR, churn mensual una vez activo Paddle

### B.3 — Recordatorios WhatsApp/SMS
> Email ya funciona completamente. SMS/WA diferido por costo operativo.
> Evaluar Twilio vs Meta Cloud API cuando haya clientes reales que lo pidan.

### B.4 — Social login (Google / Microsoft)
> DIFERIDO para v2.
> Razón: el registro tiene pasos de onboarding adicionales (nombre clínica, especialidad, etc.)
> que no existen en el flujo OAuth estándar. Complicaría la UX actual sin valor proporcional para v1.

### B.5 — CI/CD + Deployment
> DIFERIDO hasta elegir servidor.
> Candidatos: Hetzner (preferido), DigitalOcean, Network Solutions.

- [ ] GitHub Actions: correr tests en cada PR, Pint check, npm build
- [ ] Deploy automático a VPS via SSH + artisan migrate --force
- [ ] Dockerización para producción (`docker-compose.prod.yml`)
- [ ] Documentación de deployment (variables de entorno, SSL, queue worker, scheduler)

### B.6 — Múltiples sedes
> DIFERIDO hasta tener al menos 1 cliente que lo solicite.
> Schema ya preparado sin costo adicional: `clinics.parent_clinic_id` + `appointments.branch_id`.

- [ ] UI en Settings: crear/editar sedes hijas
- [ ] Selector de sede en Appointments/Create|Edit
- [ ] Filtros por sede en calendario, reportes, listados

---

## ✅ Completados

### Sprint F — UX & Onboarding (2026-05-21) ✅
F.1 Setup Checklist · F.2 Empty States · F.3 Página pública enriquecida
F.4 Tour Driver.js · F.5 Ayuda contextual + /help · F.6 Onboarding mejorado
F.7 Custom domain · F.8 Demo data toggle · F.9 Skeleton screens · F.10 Atajos de teclado

### Sprints anteriores ✅
Sprint A (Admin branding/SEO) · Sprint B (Landing pública + 4 tiers pricing)
Sprint C (Pulido listados + filtros avanzados) · Bloque 0 (Hardening + DB forward-compat + Backup + Sentry)
v1.4 (Plantillas SOAP, archivos paciente, agenda diaria, catálogo) · v1.3 (2FA TOTP)

---

## 📌 Decisiones pendientes del usuario

1. **Servidor de producción**: Hetzner (preferido) vs DigitalOcean vs Network Solutions — decidir antes de Sprint CI/CD
2. **Paddle business number**: en trámite → desbloquea B.1 (checkout real) y B.2 (MRR/churn)
3. **Recetas G.5**: revisar en Sprint G si el módulo actual es suficiente para v1
