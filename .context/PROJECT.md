# 🏥 ControClinic - Contexto del Proyecto

## Información General

- **Nombre:** ControClinic
- **Tipo:** SaaS Multi-tenant para clínicas médicas
- **Versión:** 0.2.0-alpha
- **Estado:** En desarrollo (Fase 1 - Fundación SaaS)
- **Última actualización:** 2026-01-30

## Stack Tecnológico

```yaml
Backend:
  Framework: Laravel 12
  PHP: 8.3+
  Database: MySQL 8.0 (controclinic)
  
Frontend:
  Stack: TALL (Tailwind + Alpine.js + Laravel + Livewire 3)
  CSS: Tailwind CSS 3.x + CSS Variables dinámicas
  JS: Alpine.js 3.x
  Components: Livewire 3 + Volt

Paquetes Principales:
  - livewire/livewire: ^3.7
  - livewire/volt: ^1.10
  - laravel/breeze: ^2.3 (autenticación)
  - laravel/cashier: (pagos Stripe - por configurar)
  - spatie/laravel-permission: ^6.24 (roles)
  - spatie/laravel-activitylog: ^4.10 (audit logs)

Ambiente:
  Local: Laravel Herd
  Database: MySQL 8.0
  Dominio Dev: localhost:8000
  Dominio Prod: controclinic.com (por configurar)
```

## Arquitectura Multi-tenant

```
Estrategia: Single Database con clinic_id (UUID)
Aislamiento: TenantMiddleware + Global Scopes
Route Binding: {clinic} resuelve por slug

URLs:
├── / (Landing page pública - pendiente)
├── /pricing (Precios - pendiente)
├── /register (Registro clínica - pendiente)
├── /c/{clinic_slug} (Portal público de clínica - pendiente)
├── /app/{clinic_slug}/* (Dashboard de clínica - funcionando)
└── /admin/* (Admin SaaS - pendiente)
```

## Modelo de Negocio

```yaml
Planes:
  Free: $0/mes (1 usuario, 25 pacientes, 20 citas/mes)
  Solo: $25/mes (2 usuarios, ilimitado)
  Group: $60/mes (8 usuarios, reportes)
  Enterprise: $180/mes (ilimitado + API + white-label)

Mercados:
  Primarios: LATAM + España + USA + Canadá
  Idiomas: Español, Inglés
  Monedas: 22 soportadas (USD, CAD, MXN, GTQ, EUR, etc.)
```

## Equipo y Contacto

- **Desarrollador:** Szystems
- **Repositorio:** Local (por subir a GitHub)
- **Documentación:** .context/
