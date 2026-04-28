# 📦 Documentación de Modelos

## Clinic (Tenant Principal)

```php
Tabla: clinics
Primary Key: id (UUID)

Campos:
├── id (uuid) - Identificador único
├── name (string) - Nombre de la clínica
├── slug (string, unique) - URL slug
├── email (string, unique) - Email de contacto
├── phone (string, nullable) - Teléfono
├── address (string, nullable) - Dirección
├── city (string, nullable) - Ciudad
├── country (string, default: 'GT') - País (código ISO)
├── timezone (string, default: 'America/Guatemala')
├── currency (string, default: 'USD')
├── locale (string, default: 'es')
├── owner_id (bigint, FK → users.id)
├── plan_id (bigint, FK → plans.id, nullable)
├── plan_type (enum: free/solo/group/enterprise)
├── is_manual_plan (boolean, default: false) - Cortesía/manual
├── manual_plan_reason (string, nullable) - Razón cortesía
├── status (enum: active/suspended/cancelled/trial)
├── trial_ends_at (timestamp, nullable)
├── onboarding_completed_at (timestamp, nullable)
├── settings (json, nullable) - Configuración
├── branding (json, nullable) - Personalización visual + theme
├── public_portal_enabled (boolean, default: true)
├── public_portal_slug (string, unique, nullable)
├── max_patients (integer, default: 25)
├── max_appointments_per_month (integer, default: 5)
├── max_doctors (integer, default: 1)
├── max_staff (integer, default: 0)
├── storage_used_bytes (bigint, default: 0)
├── max_storage_bytes (bigint, default: 524288000)
├── created_at, updated_at, deleted_at

Relaciones:
├── plan() → BelongsTo Plan
├── users() → HasMany User
├── owner() → BelongsTo User
├── doctors() → HasMany User (where role = doctor)
├── staff() → HasMany User (where role in assistant, secretary, receptionist)
├── invitations() → HasMany ClinicInvitation
├── patients() → HasMany Patient
├── appointments() → HasMany Appointment
└── medicalRecords() → HasMany MedicalRecord

Métodos importantes:
├── getPlanLimits(): array - Obtiene límites del plan
├── hasFeature(string): bool - Verifica si tiene feature
├── canAddPatient(): bool - Verifica límite de pacientes
├── canAddAppointmentThisMonth(): bool
├── canAddDoctor(): bool
├── canAddStaff(): bool
├── isOnTrial(): bool
├── isActive(): bool
├── getPublicUrl(): string
└── getDashboardUrl(): string

Constantes:
└── PLAN_LIMITS (array) - Configuración de límites por plan
```

## User

```php
Tabla: users
Primary Key: id (bigint auto-increment)

Campos:
├── id (bigint)
├── clinic_id (uuid, FK → clinics.id, nullable)
├── name (string)
├── email (string, unique)
├── email_verified_at (timestamp, nullable)
├── password (string)
├── role (enum: owner/doctor/assistant/secretary/receptionist/admin)
├── phone (string, nullable)
├── avatar (string, nullable)
├── locale (string, default: 'es')
├── timezone (string, nullable)
├── theme (string, default: 'light') - light/dark/system
├── specialties (json, nullable) - Para doctores
├── bio (text, nullable)
├── license_number (string, nullable) - Número colegiado
├── working_hours (json, nullable)
├── is_active (boolean, default: true)
├── is_super_admin (boolean, default: false) - Acceso panel admin SaaS
├── two_factor_enabled (boolean, default: false)
├── last_login_at (timestamp, nullable)
├── last_login_ip (string, nullable)
├── remember_token, created_at, updated_at, deleted_at

Relaciones:
├── clinic() → BelongsTo Clinic
├── patients() → HasMany Patient (como primary_doctor)
├── appointments() → HasMany Appointment (como doctor)
└── medicalRecords() → HasMany MedicalRecord (como doctor)

Métodos importantes:
├── isOwner(): bool
├── isDoctor(): bool
├── isAssistant(): bool
├── isSecretary(): bool
├── isReceptionist(): bool
├── isAdmin(): bool
├── isStaff(): bool
├── canManageClinic(): bool
├── canViewMedicalRecords(): bool
├── canManageAppointments(): bool
├── updateLastLogin(ip): void
├── getTodayAppointments(): Collection
└── getUpcomingAppointments(limit): Collection

Constantes:
├── ROLE_OWNER = 'owner'
├── ROLE_DOCTOR = 'doctor'
├── ROLE_ASSISTANT = 'assistant'
├── ROLE_SECRETARY = 'secretary'
├── ROLE_RECEPTIONIST = 'receptionist'
└── ROLE_ADMIN = 'admin'

Traits:
├── HasRoles (Spatie)
├── LogsActivity (Spatie)
└── SoftDeletes
```

## Patient

```php
Tabla: patients
Primary Key: id (UUID)

Campos:
├── id (uuid)
├── clinic_id (uuid, FK → clinics.id)
├── primary_doctor_id (bigint, FK → users.id, nullable)
├── medical_record_number (string, nullable)
├── first_name (string)
├── last_name (string)
├── email (string, nullable)
├── phone (string, nullable)
├── phone_secondary (string, nullable)
├── birth_date (date, nullable)
├── gender (enum: male/female/other, nullable)
├── id_type (string, nullable) - DPI, Pasaporte, etc.
├── id_number (string, nullable)
├── address (string, nullable)
├── city (string, nullable)
├── state (string, nullable)
├── postal_code (string, nullable)
├── country (string, default: 'GT')
├── blood_type (enum, nullable)
├── allergies (text, nullable)
├── chronic_conditions (text, nullable)
├── current_medications (text, nullable)
├── emergency_contacts (json, nullable)
├── insurance_info (json, nullable)
├── notes (text, nullable)
├── preferences (json, nullable)
├── is_active (boolean, default: true)
├── last_visit_at (timestamp, nullable)
├── created_at, updated_at, deleted_at

Relaciones:
├── clinic() → BelongsTo Clinic
├── primaryDoctor() → BelongsTo User
├── appointments() → HasMany Appointment
└── medicalRecords() → HasMany MedicalRecord

Accessors:
├── full_name → "{first_name} {last_name}"
├── age → Edad calculada desde birth_date
└── initials → Iniciales del nombre

Métodos importantes:
├── generateMedicalRecordNumber(): string
├── updateLastVisit(): void
├── hasAllergies(): bool
├── hasChronicConditions(): bool
├── getUpcomingAppointments(limit): Collection
└── getRecentMedicalRecords(limit): Collection

Scopes:
├── active() - is_active = true
├── forClinic(clinicId)
├── forDoctor(doctorId)
└── search(term) - Busca en nombre, email, teléfono, expediente

Traits:
├── BelongsToClinic (auto-filter por clinic_id)
├── LogsActivity
└── SoftDeletes
```

## Appointment

```php
Tabla: appointments
Primary Key: id (UUID)

Campos:
├── id (uuid)
├── clinic_id (uuid, FK)
├── patient_id (uuid, FK → patients.id)
├── doctor_id (bigint, FK → users.id)
├── created_by (bigint, FK → users.id, nullable)
├── appointment_type (enum: scheduled/walk_in/emergency/follow_up/telemedicine)
├── appointment_date (date)
├── start_time (time, nullable)
├── end_time (time, nullable)
├── duration_minutes (integer, default: 30)
├── queue_number (integer, nullable) - Para walk-in
├── queue_period (enum: morning/afternoon/evening, nullable)
├── status (enum: scheduled/confirmed/waiting/in_progress/completed/cancelled/no_show)
├── reason (string, nullable)
├── symptoms (text, nullable)
├── notes (text, nullable)
├── checked_in_at (timestamp, nullable)
├── started_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
├── cancelled_at (timestamp, nullable)
├── cancellation_reason (string, nullable)
├── reminder_sent (boolean, default: false)
├── reminder_sent_at (timestamp, nullable)
├── room (string, nullable)
├── resources (json, nullable)
├── is_recurring (boolean, default: false)
├── recurring_pattern_id (uuid, nullable)
├── created_at, updated_at, deleted_at

Relaciones:
├── clinic() → BelongsTo Clinic
├── patient() → BelongsTo Patient
├── doctor() → BelongsTo User
└── createdBy() → BelongsTo User

Accessors:
├── start_date_time → Carbon
├── end_date_time → Carbon
├── status_color → Color para UI
├── status_label → Label traducido
└── type_label → Label traducido

Métodos importantes:
├── isEditable(): bool
├── isCancellable(): bool
├── canCheckIn(): bool
├── canStart(): bool
├── canComplete(): bool
├── checkIn(): void
├── start(): void
├── complete(): void
├── cancel(reason): void
├── markAsNoShow(): void
├── confirm(): void
├── calculateEndTime(): string
└── conflictsWith(other): bool

Scopes:
├── forClinic(clinicId)
├── forDoctor(doctorId)
├── forPatient(patientId)
├── forDate(date)
├── forDateRange(start, end)
├── upcoming()
├── today()
├── pending()
└── active()

Constantes de Status:
├── STATUS_SCHEDULED, STATUS_CONFIRMED
├── STATUS_WAITING, STATUS_IN_PROGRESS
├── STATUS_COMPLETED, STATUS_CANCELLED
└── STATUS_NO_SHOW

Constantes de Tipo:
├── TYPE_SCHEDULED, TYPE_WALK_IN
├── TYPE_EMERGENCY, TYPE_FOLLOW_UP
└── TYPE_TELEMEDICINE
```

## MedicalRecord

```php
Tabla: medical_records
Primary Key: id (UUID)

Campos:
├── id (uuid)
├── clinic_id (uuid, FK)
├── patient_id (uuid, FK)
├── doctor_id (bigint, FK)
├── appointment_id (uuid, FK, nullable)
├── record_type (enum: consultation/diagnosis/prescription/lab_result/imaging/procedure/surgery/referral/follow_up_note/vital_signs/vaccination/other)
├── title (string, nullable)
├── content (longText, nullable)
├── chief_complaint (text, nullable)
├── present_illness (text, nullable)
├── physical_examination (text, nullable)
├── assessment (text, nullable)
├── plan (text, nullable)
├── vital_signs (json, nullable)
├── diagnoses (json, nullable) - CIE-10
├── prescriptions (json, nullable)
├── attachments (json, nullable)
├── is_confidential (boolean, default: false)
├── visible_to_roles (json, nullable)
├── status (enum: draft/final/amended/deleted)
├── finalized_at (timestamp, nullable)
├── created_at, updated_at, deleted_at

Relaciones:
├── clinic() → BelongsTo Clinic
├── patient() → BelongsTo Patient
├── doctor() → BelongsTo User
└── appointment() → BelongsTo Appointment

Métodos importantes:
├── isDraft(): bool
├── isFinalized(): bool
├── isEditable(): bool
├── finalize(): void
├── amend(): void
├── canBeViewedBy(user): bool
├── getFormattedVitalSigns(): array
└── soapTemplate(): array (static)

Scopes:
├── forClinic(clinicId)
├── forPatient(patientId)
├── forDoctor(doctorId)
├── ofType(type)
├── consultations()
├── prescriptions()
├── finalized()
├── drafts()
├── notConfidential()
└── visibleToRole(role)

Constantes de Tipo:
├── TYPE_CONSULTATION, TYPE_DIAGNOSIS
├── TYPE_PRESCRIPTION, TYPE_LAB_RESULT
├── TYPE_IMAGING, TYPE_PROCEDURE
├── TYPE_SURGERY, TYPE_REFERRAL
├── TYPE_FOLLOW_UP_NOTE, TYPE_VITAL_SIGNS
├── TYPE_VACCINATION, TYPE_OTHER

Constantes de Status:
├── STATUS_DRAFT
├── STATUS_FINAL
├── STATUS_AMENDED
└── STATUS_DELETED
```

## Plan

```php
Tabla: plans
Primary Key: id (bigint auto-increment)

Campos:
├── id (bigint)
├── name (string) - Nombre visible (Free/Solo/Group/Enterprise)
├── slug (string, unique) - Identificador interno
├── description (string, nullable)
├── max_patients (integer, nullable) - null = ilimitado
├── max_appointments_per_month (integer, nullable)
├── max_doctors (integer, nullable)
├── max_staff (integer, nullable)
├── max_storage_bytes (bigint, nullable)
├── features (json, nullable) - Array de features habilitados
├── monthly_price (decimal:2)
├── yearly_price (decimal:2)
├── paddle_monthly_price_id (string, nullable)
├── paddle_yearly_price_id (string, nullable)
├── paddle_product_id (string, nullable)
├── trial_days (integer, default: 14)
├── sort_order (integer, default: 0)
├── is_active (boolean, default: true)
├── is_popular (boolean, default: false)
├── is_free (boolean, default: false) - Cortesía/regalo
├── is_enterprise (boolean, default: false)
├── created_at, updated_at

Relaciones:
└── clinics() → HasMany Clinic

Notas:
- El plan Free es CORTESÍA: solo asignable desde admin.
- En registro nuevo se asigna 'solo' con trial 14 días.
- Ver DECISIONS.md ADR-006.
```

## ClinicInvitation

```php
Tabla: clinic_invitations
Primary Key: id (UUID)

Campos:
├── id (uuid)
├── clinic_id (uuid, FK → clinics.id)
├── email (string)
├── name (string, nullable)
├── role (enum: doctor/assistant/secretary/receptionist)
├── token (string, unique) - URL de aceptación
├── invited_by (bigint, FK → users.id)
├── expires_at (timestamp)
├── accepted_at (timestamp, nullable)
├── cancelled_at (timestamp, nullable)
├── created_at, updated_at

Relaciones:
├── clinic() → BelongsTo Clinic
└── inviter() → BelongsTo User (invited_by)

Métodos:
├── isPending(): bool
├── isExpired(): bool
├── isAccepted(): bool
└── isCancelled(): bool

Scopes:
├── pending() - sin aceptar/cancelar y no expiradas
└── forClinic(clinicId)

Flujo:
1. Owner/Admin crea invitación → email con token
2. Receptor entra a /invitation/{token} → setea password
3. Crea User con clinic_id + role + email_verified_at = now
```
