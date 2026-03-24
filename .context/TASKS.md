# 📝 Tareas Pendientes

> Actualizado: 2026-03-23
> Enfoque: SaaS-First

---

## ✅ Completado - INFRAESTRUCTURA

### 0.1 Docker + Migración WSL ✅
- [x] Crear Dockerfile (PHP 8.3-FPM + extensiones)
- [x] Crear docker-compose.yml (app, nginx, mysql, redis)
- [x] Crear docker/nginx/default.conf
- [x] Crear docker/php/php.ini (configuración optimizada)
- [x] Crear .dockerignore y .env.docker
- [x] Migrar proyecto a WSL Ubuntu (/home/szott/proyectos/controclinic)
- [x] Verificar que todo funcione en Docker (http://localhost:8088)
- [ ] Actualizar README con instrucciones Docker

---

## 📅 Prioridad Media - FASE 1 SaaS

### 1.1 Landing Page Pública
- [ ] Crear layout público (sin autenticación)
- [ ] Hero section con propuesta de valor
- [ ] Sección de features/beneficios
- [ ] Testimonials placeholders
- [ ] Footer con links legales
- [ ] Mobile responsive

### 1.2 Página de Precios
- [ ] Tabla comparativa de planes (Free, Solo, Group, Enterprise)
- [ ] Toggle mensual/anual
- [ ] CTAs a registro
- [ ] FAQ de precios

### 1.3 Sistema de Registro de Clínicas
- [ ] Ruta /register para nuevas clínicas
- [ ] Formulario: nombre clínica, nombre owner, email, password, país
- [ ] Verificación de email
- [ ] Creación automática de Clinic + User (owner)
- [ ] Slug único generado automáticamente
- [ ] Configuración por defecto según país (timezone, moneda)
- [ ] Redirección a onboarding

### 1.4 Onboarding Wizard (5 pasos)
- [ ] Componente Livewire multi-step
- [ ] Paso 1: Datos de la clínica (nombre, dirección, teléfono, especialidad)
- [ ] Paso 2: Personalización (logo, colores con preview)
- [ ] Paso 3: Horarios de atención (días, horas)
- [ ] Paso 4: Perfil del doctor (especialidad, colegiado, foto)
- [ ] Paso 5: Resumen + tutorial + CTA al dashboard
- [ ] Marcar onboarding como completado
- [ ] Skip option para usuarios que quieran configurar después

---

## � Prioridad Siguiente - FASE 1 Continuación

### 1.5 Integración Paddle
- [ ] Configurar Laravel Cashier Paddle (ya instalado ^2.6)
- [ ] Crear productos en Paddle Dashboard
- [ ] Página de checkout para upgrade
- [ ] Portal de facturación del cliente
- [ ] Webhooks: subscription.created, updated, deleted
- [ ] Trial de 14 días para planes pagos
- [ ] Lógica de cancelación y downgrade

---

## 📅 Prioridad Baja (Este Mes) - FASE 2

### Panel Admin SaaS
- [ ] Ruta /admin con autenticación separada
- [ ] Dashboard: total clínicas, MRR, nuevos registros, churn
- [ ] Lista de clínicas con filtros (plan, status, fecha)
- [ ] Detalle de clínica (usuarios, actividad, plan)
- [ ] Acciones: suspender, extender trial, cambiar plan
- [ ] Impersonar usuario para soporte

### Límites por Plan
- [ ] Middleware `CheckPlanLimits`
- [ ] Verificar límite de usuarios al crear
- [ ] Verificar límite de pacientes
- [ ] Verificar límite de citas/mes
- [ ] Mostrar uso actual en dashboard
- [ ] Banners de upgrade cuando cerca del límite
- [ ] Modal de upgrade con beneficios

### Portal Público de Clínica
- [ ] URL: /c/{clinic-slug}
- [ ] Información de la clínica
- [ ] Horarios de atención
- [ ] Lista de doctores
- [ ] Formulario de booking online
- [ ] Confirmación por email

---

## ✅ Completado Recientemente

### Sistema de Citas ✅ (2026-01-30)
- [x] `App\Livewire\App\Appointments\Index` - Lista con filtros y workflow
- [x] `App\Livewire\App\Appointments\Create` - Con búsqueda de paciente
- [x] `App\Livewire\App\Appointments\Edit` - Edición con validación
- [x] `App\Livewire\App\Appointments\Show` - Detalle con timeline
- [x] Workflow: confirmar, check-in, iniciar, completar, cancelar, no-show
- [x] Validación de conflictos de horario (checkConflicts)
- [x] Traducciones appointments.php (ES/EN)

### Traducciones y Selector de Idioma ✅ (2026-01-30)
- [x] Traducciones EN: patients.php, settings.php
- [x] Selector de idioma en navegación (desktop + móvil)
- [x] Ruta lang.switch con sesión
- [x] TenantMiddleware prioriza sesión > clínica > default
- [x] Nav links usando claves de traducción

### CRUD Pacientes ✅
- [x] Crear `app/Livewire/App/Patients/Create.php`
- [x] Crear `app/Livewire/App/Patients/Edit.php`
- [x] Crear `app/Livewire/App/Patients/Show.php`
- [x] Lista con búsqueda y paginación
- [x] Validaciones completas
- [x] Activity Log funcionando

### Módulo Settings ✅
- [x] 6 tabs de configuración
- [x] Guardado por sección
- [x] Upload de logo
- [x] Colores dinámicos funcionando

### Navegación ✅
- [x] Links a Dashboard, Pacientes, Citas
- [x] Icono de Settings (⚙️)
- [x] Menú móvil responsive
- [x] Selector de idioma ES/EN

### Colores Dinámicos ✅
- [x] CSS Variables
- [x] Override de indigo-* classes
- [x] btn-primary, bg-primary disponibles

---

## 🐛 Bugs y Mejoras Técnicas

### Bugs Conocidos
- [x] Activity Log no soportaba UUIDs → FIXED

### Deuda Técnica
- [ ] Agregar PHPDoc a métodos públicos
- [ ] Crear factories para testing
- [ ] Configurar CI/CD básico
- [ ] Tests unitarios básicos

### Seguridad
- [ ] Rate limiting en rutas públicas
- [ ] CSRF en formularios Livewire
- [ ] Sanitización de inputs
- [ ] Políticas de autorización (Policies)

---

## 📚 Documentación

- [ ] README.md del proyecto
- [ ] Guía de instalación local
- [ ] Documentación de API (cuando exista)
- [ ] Guía de contribución
- [ ] Changelog

---

## 💡 Ideas para Futuro

- [ ] Importación de pacientes desde CSV/Excel
- [ ] Exportación de datos
- [ ] Reportes PDF
- [ ] Integración con laboratorios
- [ ] Recetas electrónicas con QR
- [ ] Chat interno entre staff
- [ ] Recordatorios automáticos
- [ ] Encuestas de satisfacción

---

## Notas

- Priorizar features que generen valor para el usuario
- Mantener código simple y mantenible
- Testing antes de features complejas
- Documentar decisiones importantes en `.context/DECISIONS.md`
