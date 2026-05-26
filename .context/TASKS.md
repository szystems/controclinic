# 📝 Tareas Pendientes

> Actualizado: 2026-05-25
> Estado: 588 tests / 1270 asserts · Pint clean · Sprint G completado
> Sprints completados: A ✅ B ✅ C ✅ F.1→F.10 ✅ G ✅

---

## ✅ Sprint G — Pulido, Pagos y Admin (COMPLETADO — 2026-05-25)

### G.1 — Fixes de branding ✅ (commits `db0521c`, `7dab2af`, `208d061`, `a6638b9`)
- Nav público: logo sin texto redundante cuando hay logo configurado
- Auth views: `<x-app-logo>` en lugar de SVG hardcoded

### G.2 — Pagos parciales en Facturación ✅ (commit `f025372` — 19 tests)
- `openPaymentModal()`, `savePayment()`, `deletePayment()` implementados
- `amount_due = total - sum(payments)` · status → `paid` automático cuando amount_due = 0
- `InvoicePartialPaymentsTest` — 19 tests pasando

### G.3 — Panel Admin: métricas ✅ (commit `e282731` + `eed6104`)
- KPIs: total clínicas, activas, free/paid, nuevas 30 días
- Gráfica semanal últimas 12 semanas · Tabla recientes con último login
- Fix: `last_login_at` ahora se registra via `Event::listen(Login::class, ...)`

### G.4 — Tests SetupChecklist + EmptyStates ✅ (commit `807d6ca`)
- `SetupChecklistTest` — 11 tests · `EmptyStatesTest` — 8 tests

### G.5 — Módulo Recetas ✅ (ya completo — 14 tests en `PrescriptionsTest`)
- Index, Show, Create, Edit implementados con permisos correctos
- Folio RX-XXXX por clínica, QR payload, PDF via DomPDF
- Policy `PrescriptionPolicy` + multi-tenant isolation

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

## ⚠️ Pre-lanzamiento — Obligatorio antes de publicar

> Estas tareas no son de código puro. Son requisitos legales/de contenido que deben resolverse
> antes de abrir la app al público. Ninguna bloquea el desarrollo actual.

### PL.1 — Textos legales reales
> `lang/es/legal.php` contiene texto placeholder con aviso "versión preliminar pendiente de revisión legal".
> La app maneja datos médicos de pacientes — Privacy Policy y Terms of Service deben ser documentos reales.

- [ ] Redactar o adaptar Privacy Policy real para datos médicos (HIPAA-like / GDPR-like según países objetivo)
- [ ] Redactar o adaptar Terms of Service definitivos
- [ ] Actualizar `lang/es/legal.php` con el texto final
- [ ] Crear `lang/en/legal.php` con la versión EN (si se lanza en inglés)

### PL.2 — Traducciones del sitio público (EN)
> Las páginas públicas (`home`, `pricing`, `contact`) tienen texto hardcodeado en español.
> El middleware ya detecta el idioma del navegador, pero no hay nada que traducir aún.

- [ ] Definir primero el contenido FINAL de home.blade.php (testimonios reales, números reales)
- [ ] Crear `lang/es/public.php` y `lang/en/public.php`
- [ ] Reemplazar texto hardcodeado en `home`, `pricing`, `contact` por `__('public.clave')`
- [ ] Nota: `privacy.blade.php` y `terms.blade.php` ya usan `__()` — solo falta el contenido en lang/

### PL.3 — Contenido marketing real en home
> home.blade.php tiene placeholders evidentes que deben ser reemplazados por datos reales:
> - Testimonios ficticios (Dra. María González, Dr. Juan Pablo Méndez, etc.)
> - "Más de 500 clínicas" — número inventado
> - Logos de clientes ficticios (ClinicaPlus, MediCare, SaludTotal)
> - FAQ: verificar si "50% descuento para estudiantes" es una promesa real

- [ ] Reemplazar testimonios placeholder por testimonios reales (o eliminar sección si no hay aún)
- [ ] Ajustar número de clínicas a uno real o eliminar el claim
- [ ] Eliminar logos ficticios o reemplazar por clientes reales
- [ ] Confirmar/ajustar FAQ antes de publicar

---

## 📌 Decisiones pendientes del usuario

1. **Servidor de producción**: Hetzner (preferido) vs DigitalOcean vs Network Solutions — decidir antes de Sprint CI/CD
2. **Paddle business number**: en trámite → desbloquea B.1 (checkout real) y B.2 (MRR/churn)
3. **Recetas G.5**: revisar en Sprint G si el módulo actual es suficiente para v1
