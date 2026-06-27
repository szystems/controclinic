# 🗺️ Roadmap — Ruta a v1.0 y Lanzamiento

> Este archivo es el mapa de alto nivel. **Orden de ejecución:** [LAUNCH-PLAN.md](LAUNCH-PLAN.md).
> Detalle de tareas: [TASKS.md](TASKS.md) · Estado: [STATUS.md](STATUS.md) · Deploy: [DEPLOYMENT.md](DEPLOYMENT.md).

## Estado actual: 0.6.0-alpha → objetivo **1.0.0 (lanzamiento público)**

El producto está **funcionalmente completo** para v1 (588 tests, todos los módulos clínicos implementados).
**Producción live** desde 2026-06-27 (`controclinic.com`). Lo que falta: **operación segura del panel admin (Fase G)**, monetización (Paddle), coherencia marca/mensaje, legal/marketing, y checklist go-live.

---

## 🏁 Hitos hacia v1.0

Orden de ejecución: **A → B → C → G → D → E → F** (ver [LAUNCH-PLAN.md](LAUNCH-PLAN.md)). A y B en paralelo; **G antes de F**.

### Fase A — Infraestructura y dominio 🌐 ✅ ~85%
> Dominio **Network Solutions** · DNS **Cloudflare** · app **Hetzner/Coolify** (ADR-013).
- [x] controclinic.com + deploy Coolify · `/up` 200
- [ ] Smoke test completo · Resend · snapshot VPS

### Fase G — Panel Admin operaciones 🔐
> CRUD super admins + cambio contraseña en `/admin` (gap detectado en prod).
- Antes del go-live · paralelo con B/C

### Fase B — Planes BD fuente única 📊
> Límites/precios/features solo en tabla `plans`; cambios vía Admin sin tocar código.
- Fix `plan_id` en registro · enum plan_type · eliminar duplicación `PLAN_LIMITS`
- Plan privado con código de descuento · `trial_days = 0`

### Fase C — Marca y freemium 🎯
- ControClinic by SZ Systems (ADR-011)
- Mensaje freemium coherente — sin trial en pagos (ADR-012)
- Login con CTA registro (C6) · szystems.com: sección ControClinic

### Fase D — Paddle 💳
- Cuenta SZ Systems · productos ControClinic — {Plan} · checkout · webhooks · sin trial

### Fase E — Legal y marketing 📄
- Privacy/Terms reales · home bilingüe · contenido real

### Fase F — Go-live ✅
- E2E prod · Paddle live · release **1.0.0**

---

## ✅ Completado (base sobre la que se construye v1)

### Q1 2026 — Fundaciones
- Auth Breeze + Livewire · Multi-tenant por slug (TenantMiddleware + BelongsToClinic)
- CRUD Pacientes, Citas, Historial médico, Staff, Settings (6 tabs)
- Roles & Permisos Spatie + Activity Log · Panel Super-Admin SaaS
- Onboarding wizard · Páginas públicas + Portal público (booking) · Notificaciones email

### Q1–Q2 2026 — Sprints A → G
- A: Admin branding/SEO · B: Landing + 4 tiers · C: Pulido listados/filtros
- Bloque 0: Hardening + forward-compat DB + Backup (spatie) + Sentry + Rate limiters
- v1.3: 2FA TOTP · v1.4: Plantillas SOAP, archivos paciente, agenda diaria, catálogo
- F.1–F.10: Setup checklist, empty states, página pública enriquecida, tour Driver.js,
  ayuda contextual, onboarding mejorado, custom domain, demo data, skeletons, atajos teclado
- G: Branding auth, pagos parciales facturación, métricas admin, módulo recetas

---

## 🌅 Post-v1 (diferido — no bloquea lanzamiento)

- Métricas de ingresos avanzadas (MRR/ARR/churn) una vez Paddle live
- Recordatorios SMS/WhatsApp (Twilio vs Meta Cloud API) — cuando lo pidan clientes
- Social login (Google/Microsoft) — v2, requiere repensar onboarding
- Múltiples sedes (schema ya preparado) — cuando haya demanda
- App móvil (lectura) · API REST pública · IA (resúmenes de consulta)
- White-label completo (Enterprise)
