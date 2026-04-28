# 🏥 ControClinic - Contexto del Proyecto

## Información General

- **Nombre:** ControClinic
- **Tipo:** SaaS Multi-tenant para clínicas médicas
- **Versión:** 0.6.0-alpha
- **Estado:** Sprint de Estabilización (pre-Fase 4)
- **Última actualización:** 2026-04-28
- **Repositorio:** github.com/szystems/controclinic

## Stack Tecnológico

```yaml
Backend:
  Framework: Laravel 12.49
  PHP: 8.3+
  Database (Docker): MySQL 8.0
  Database (tests): SQLite in-memory
  Cache/Queue/Sessions: Redis 7

Frontend:
  Stack: TALL (Tailwind + Alpine.js + Laravel + Livewire 3)
  CSS: Tailwind 3.4 + CSS Variables dinámicas (branding por clínica)
  JS: Alpine.js 3.x
  Build: Vite 7

Paquetes Principales:
  - livewire/livewire: ^3.7
  - livewire/volt: ^1.10
  - laravel/breeze: ^2.4 (autenticación)
  - laravel/cashier-paddle: ^2.8 (pagos Paddle)
  - spatie/laravel-permission: ^6.24 (roles + permisos)
  - spatie/laravel-activitylog: ^4.12 (audit logs)
  - mcamara/laravel-localization: ^2.4 (i18n)

Ambiente Docker (controclinic-*):
  nginx     → http://localhost:8088   (entrada principal)
  app       → PHP 8.3-FPM (interno 9000)
  mysql     → localhost:33060 (DB controclinic)
  redis     → localhost:63790
  mailpit   → http://localhost:8025   (SMTP testing)
  phpmyadmin→ http://localhost:8089
  WSL: Ubuntu — /home/szott/proyectos/controclinic
```

## Arquitectura Multi-tenant

```
Estrategia: Single Database con clinic_id (UUID)
Aislamiento: TenantMiddleware + BelongsToClinic trait (Global Scope automático)
Route Binding: {clinic} resuelve por slug o public_portal_slug

URLs implementadas:
├── /                          ✅ Landing pública
├── /pricing                   ✅ Página de precios
├── /contact                   ✅ Contacto
├── /register                  ✅ Registro de clínica
├── /login                     ✅ Auth (Breeze + Livewire)
├── /invitation/{token}        ✅ Aceptar invitación staff
├── /c/{slug}                  ✅ Portal público de clínica (booking)
├── /public/{slug}             ✅ Alias del portal
├── /app/{clinic}/dashboard    ✅ Panel clínica
├── /app/{clinic}/patients     ✅ CRUD pacientes
├── /app/{clinic}/appointments ✅ CRUD citas (lista; calendario pendiente)
├── /app/{clinic}/staff        ✅ Gestión equipo + invitaciones
├── /app/{clinic}/billing      ✅ Suscripción Paddle
├── /app/{clinic}/settings     ✅ 6 tabs configuración
└── /admin/*                   ✅ Panel super-admin SaaS

URLs pendientes:
├── /terms, /privacy           ⏳ Páginas legales (placeholder)
├── /app/{clinic}/records      ⏳ Historial médico (Fase 5)
└── /app/{clinic}/profile      ⏳ Perfil propio (Fase 7)
```

## Modelo de Negocio

```yaml
Planes (BD: tabla plans):
  Free:       $0    — Cortesía, asignado solo desde Admin (no autoservicio)
  Solo:       $29 / $23 anual  — 1 doctor, 1 staff, ilimitado pacientes/citas
  Group:      $79 / $63 anual  — 5 doctores, 3 staff, reportes
  Enterprise: Personalizado    — Ilimitado, white-label, API

Política de cuenta (ver DECISIONS.md ADR-008):
  - Registro nuevo → trial 14 días en plan Solo
  - Trial expirado → modo READ-ONLY (puede ver datos, no crear)
  - Plan pagado expirado → READ-ONLY hasta reactivar
  - Suspended (admin) → solo billing
  - Cancelled → sólo billing
  - Plan Free es cortesía: sin trial_ends_at, capacidades plenas

Mercados:
  Primarios: LATAM + España + USA + Canadá
  Idiomas: Español, Inglés (selector ES/EN)
  Monedas: 22 soportadas (USD, CAD, MXN, GTQ, EUR, etc.)
  Pagos: Paddle Sandbox (cuenta Seller ID 53650, productos creados)
```

## Equipo y Contacto

- **Desarrollador:** Szystems
- **Repositorio:** github.com/szystems/controclinic
- **Documentación:** `.context/`
