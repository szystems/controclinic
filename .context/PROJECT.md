# 🏥 ControClinic - Contexto del Proyecto

## Información General

- **Nombre:** ControClinic
- **Tipo:** SaaS Multi-tenant para clínicas médicas
- **Versión:** 0.3.0-alpha
- **Estado:** En desarrollo (Fase 1 - Fundación SaaS)
- **Última actualización:** 2026-03-23
- **Repositorio:** github.com/szystems/controclinic

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
  - livewire/livewire: ^3.6
  - livewire/volt: ^1.7
  - laravel/breeze: ^2.3 (autenticación)
  - laravel/cashier-paddle: ^2.6 (pagos Paddle)
  - spatie/laravel-permission: ^6.24 (roles)
  - spatie/laravel-activitylog: ^4.10 (audit logs)
  - mcamara/laravel-localization: ^2.3 (i18n)

Ambiente:
  Local: Docker (PHP 8.3, MySQL 8.0, Node 20, Redis)
  WSL: Ubuntu en \\wsl.localhost\Ubuntu\home\szott\proyectos
  Dominio Dev: localhost:8080
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
- **Repositorio:** github.com/szystems/controclinic
- **Documentación:** .context/
