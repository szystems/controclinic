# 🗺️ Roadmap de Desarrollo

> Actualizado: 2026-01-30
> Enfoque: SaaS-First

## Visión General

```
Fase 1 (SaaS Foundation) → Semana 1-3  → Landing + Registro + Stripe
Fase 2 (Admin Panel)     → Semana 4    → Panel Admin SaaS
Fase 3 (Plan Limits)     → Semana 5    → Límites y monetización
Fase 4 (Core Features)   → Semana 6-7  → Citas completo
Fase 5 (Public Portal)   → Semana 8    → Booking online
Fase 6 (Launch)          → Semana 9-10 → Testing + Beta Launch
```

---

## Fase 1: Fundación SaaS ⏱️ 2-3 semanas

**Objetivo:** Permitir que clínicas se registren y paguen

### Sprint 1.1 - Landing Page ⬜
- [ ] Hero section con propuesta de valor
- [ ] Features/beneficios
- [ ] Página de precios comparativa
- [ ] Footer con legales
- [ ] SEO básico

### Sprint 1.2 - Sistema de Registro ⬜
- [ ] Formulario de registro de clínica
- [ ] Verificación de email
- [ ] Creación automática Clinic + User
- [ ] Configuración por defecto según país

### Sprint 1.3 - Onboarding Wizard ⬜
- [ ] Paso 1: Datos de la clínica
- [ ] Paso 2: Personalización (logo, colores)
- [ ] Paso 3: Horarios de atención
- [ ] Paso 4: Perfil del doctor
- [ ] Paso 5: Resumen y tutorial

### Sprint 1.4 - Integración Stripe ⬜
- [ ] Configurar Laravel Cashier
- [ ] Productos en Stripe
- [ ] Checkout de suscripción
- [ ] Trial de 14 días
- [ ] Webhooks

---

## Fase 2: Panel Admin SaaS ⏱️ 1-2 semanas

**Objetivo:** Gestionar el negocio SaaS

### Features
- [ ] Dashboard métricas (MRR, clínicas, churn)
- [ ] Lista y gestión de clínicas
- [ ] Gestión de suscripciones
- [ ] Impersonación de usuarios
- [ ] Cupones y descuentos

---

## Fase 3: Límites por Plan ⏱️ 1 semana

**Objetivo:** Monetización efectiva

### Features
- [ ] Middleware CheckPlanLimits
- [ ] Límites: usuarios, pacientes, citas
- [ ] Upgrade prompts
- [ ] Banners de uso

---

## Fase 4: Funcionalidades Core ⏱️ 2-3 semanas

**Objetivo:** Producto mínimo viable completo

### Sistema de Citas
- [ ] Calendario visual (día/semana/mes)
- [ ] CRUD de citas
- [ ] Estados y workflow
- [ ] Notas de cita

### Notificaciones
- [ ] Email de confirmación
- [ ] Recordatorio 24h antes
- [ ] Notificación de cancelación

### Dashboard Mejorado
- [ ] Citas de hoy
- [ ] Estadísticas rápidas
- [ ] Acciones rápidas

---

## Fase 5: Portal Público ⏱️ 1-2 semanas

**Objetivo:** Booking online para pacientes

### Features
- [ ] Página pública de clínica (/c/{slug})
- [ ] Información y horarios
- [ ] Lista de doctores
- [ ] Formulario de booking
- [ ] Confirmación por email

---

## Fase 6: Lanzamiento Beta ⏱️ 1-2 semanas

**Objetivo:** Producto listo para usuarios reales

### Features
- [ ] Testing E2E
- [ ] Bug fixes
- [ ] Documentación usuario
- [ ] Deploy a producción
- [ ] 🚀 LANZAMIENTO BETA

---

## Completado ✅

### Infraestructura Base ✅
- [x] Laravel 12 + TALL Stack
- [x] MySQL configurado
- [x] Multi-tenancy por clinic_id
- [x] Route Model Binding
- [x] TenantMiddleware

### Modelos ✅
- [x] Clinic (UUID, settings JSON, branding JSON)
- [x] User (roles, clinic_id)
- [x] Patient (UUID, datos médicos)
- [x] Appointment (estructura)
- [x] MedicalRecord

### CRUD Pacientes ✅
- [x] Create, Edit, Show
- [x] Lista con búsqueda
- [x] Validaciones
- [x] Activity Log

### Módulo Settings ✅
- [x] 6 tabs de configuración
- [x] Upload de logo
- [x] Colores dinámicos
- [x] Zonas horarias completas
- [x] 22 monedas soportadas

### Sistema de Colores ✅
- [x] CSS Variables dinámicas
- [x] Override de clases indigo
- [x] btn-primary, bg-primary

### Navegación ✅
- [x] Dashboard, Pacientes, Citas
- [x] Settings (⚙️)
- [x] Menú móvil
- Integraciones contables
- API pública documentada
- Mobile apps enterprise
- Business Intelligence

---

## Fase 5: Enterprise

**Objetivo:** Hospitales y clínicas grandes

### Features Planificadas
- DICOM viewer básico
- Gestión de personal completa
- Multi-sede
- HL7 FHIR compliance
- Compliance reporting automático
- White-label completo
- Marketplace de integraciones
- Revenue sharing

---

## Priorización de Features

### Must Have (MVP)
1. CRUD Pacientes
2. Sistema de citas básico
3. Calendario
4. Historiales básicos
5. Planes Free + Solo

### Should Have (Fase 2)
1. Notificaciones
2. Portal paciente
3. Farmacia básica
4. Templates especialidades

### Could Have (Fase 3+)
1. WhatsApp
2. Telemedicina
3. IA avanzada
4. Analytics

### Won't Have (Por ahora)
1. App nativa compleja
2. Integraciones hospitalarias
3. Machine learning custom
4. Multi-sede

---

## Dependencias Técnicas

```yaml
Fase 1:
  - Ninguna externa crítica

Fase 2:
  - Twilio (SMS)
  - Mailgun/SES (Email)
  - OpenAI API (IA)
  
Fase 3:
  - WhatsApp Business API
  - Zoom/Twilio Video
  - Google Calendar API

Fase 4:
  - AWS Transcribe o similar
  - Stripe Connect (pagos pacientes)
  - CDN para archivos

Fase 5:
  - DICOM libraries
  - HL7 FHIR libraries
  - Custom ML models
```

---

## Criterios de Éxito por Fase

### Fase 1 (MVP)
- [ ] Doctor puede gestionar pacientes
- [ ] Doctor puede agendar citas
- [ ] Sistema de pagos funcional
- [ ] 5 clínicas beta testing

### Fase 2
- [ ] Notificaciones funcionando
- [ ] 20 clínicas activas
- [ ] MRR > $500

### Fase 3
- [ ] WhatsApp integrado
- [ ] 50 clínicas activas
- [ ] MRR > $2,000

### Fase 4
- [ ] API documentada
- [ ] 100 clínicas activas
- [ ] MRR > $5,000

### Fase 5
- [ ] Enterprise contracts
- [ ] 150+ clínicas
- [ ] MRR > $10,000
