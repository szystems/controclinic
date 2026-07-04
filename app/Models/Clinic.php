<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Paddle\Billable;

class Clinic extends Model
{
    use Billable, HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'timezone',
        'currency',
        'locale',
        'owner_id',
        'plan_id',
        'plan_type',
        'is_manual_plan',
        'manual_plan_reason',
        'status',
        'trial_ends_at',
        'onboarding_completed_at',
        'settings',
        'branding',
        'public_portal_enabled',
        'public_portal_slug',
        'public_description',
        'public_cover_image_url',
        'public_services',
        'public_show_doctors',
        'public_seo_title',
        'public_seo_description',
        'max_patients',
        'max_appointments_per_month',
        'max_doctors',
        'max_staff',
        'storage_used_bytes',
        'max_storage_bytes',
        'parent_clinic_id',
        'legal_entity_id',
        'data_retention_years',
        'sms_notifications_enabled',
        'sms_provider',
        'custom_domain',
        'custom_domain_verified_at',
        'custom_domain_txt_token',
    ];

    protected $casts = [
        'settings' => 'array',
        'branding' => 'array',
        'public_portal_enabled' => 'boolean',
        'public_services' => 'array',
        'public_show_doctors' => 'boolean',
        'is_manual_plan' => 'boolean',
        'trial_ends_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'max_patients' => 'integer',
        'max_appointments_per_month' => 'integer',
        'max_doctors' => 'integer',
        'max_staff' => 'integer',
        'storage_used_bytes' => 'integer',
        'max_storage_bytes' => 'integer',
        'data_retention_years' => 'integer',
        'sms_notifications_enabled' => 'boolean',
        'custom_domain_verified_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'doctor');
    }

    /**
     * Practicantes médicos: doctores invitados + el owner si ejerce como médico.
     * Útil para UI / selectores de doctor / calendario donde el owner típicamente atiende.
     * Para validar límites del plan usa {@see canAddDoctor()} que sólo cuenta `role=doctor`.
     */
    public function practitioners(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', ['doctor', 'owner']);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', ['assistant', 'secretary', 'receptionist']);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ClinicInvitation::class);
    }

    public function pendingInvitations(): HasMany
    {
        return $this->hasMany(ClinicInvitation::class)
            ->whereNull('accepted_at')
            ->whereNull('cancelled_at')
            ->where('expires_at', '>', now());
    }

    // ==================== PLAN & LIMITS ====================

    public function resolvePlan(): ?Plan
    {
        if ($this->plan_id) {
            return $this->relationLoaded('plan') ? $this->plan : $this->plan()->first();
        }

        if ($this->plan_type) {
            return Plan::findBySlug($this->plan_type);
        }

        return null;
    }

    public function planSlug(): string
    {
        return $this->resolvePlan()?->slug ?? $this->plan_type ?? 'free';
    }

    public function isOnFreePlan(): bool
    {
        $plan = $this->resolvePlan();

        if ($plan) {
            return (bool) $plan->is_free;
        }

        return $this->plan_type === 'free';
    }

    public function isEnterprisePlan(): bool
    {
        return (bool) ($this->resolvePlan()?->is_enterprise ?? $this->plan_type === 'enterprise');
    }

    /**
     * Whether the clinic can move to a higher tier (billing page has something to offer).
     */
    public function canUpgradePlan(): bool
    {
        if ($this->isEnterprisePlan()) {
            return false;
        }

        if ($this->isOnFreePlan() && $this->is_manual_plan) {
            return false;
        }

        $currentSort = $this->resolvePlan()?->sort_order ?? 0;

        return Plan::query()
            ->active()
            ->where('is_free', false)
            ->where('sort_order', '>', $currentSort)
            ->where(function ($query) {
                $query->where('is_private', false)
                    ->orWhereIn('slug', $this->unlockedPlanSlugs());
            })
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function unlockedPlanSlugs(): array
    {
        $slugs = data_get($this->settings, 'billing.unlocked_plan_slugs', []);

        return is_array($slugs) ? array_values(array_unique($slugs)) : [];
    }

    public function hasUnlockedPlan(Plan $plan): bool
    {
        return in_array($plan->slug, $this->unlockedPlanSlugs(), true);
    }

    public function unlockPlan(Plan $plan): void
    {
        if ($this->hasUnlockedPlan($plan)) {
            return;
        }

        $settings = $this->settings ?? [];
        $settings['billing']['unlocked_plan_slugs'] = array_merge(
            $this->unlockedPlanSlugs(),
            [$plan->slug]
        );

        $this->update(['settings' => $settings]);
    }

    public function applyPlan(Plan $plan, bool $activate = false): void
    {
        $payload = array_merge(
            [
                'plan_id' => $plan->id,
                'plan_type' => $plan->slug,
            ],
            $plan->limitsForClinicColumns()
        );

        if ($activate) {
            $payload['status'] = 'active';
        }

        $this->update($payload);
    }

    public function getPlanLimits(): array
    {
        $plan = $this->resolvePlan();

        if ($plan) {
            return $plan->getLimitsArray();
        }

        if ($this->hasDenormalizedLimits()) {
            return [
                'max_patients' => $this->max_patients,
                'max_appointments_per_month' => $this->max_appointments_per_month,
                'max_doctors' => $this->max_doctors,
                'max_staff' => $this->max_staff,
                'max_storage_bytes' => $this->max_storage_bytes,
                'features' => Plan::getFreePlan()?->features ?? [],
            ];
        }

        $freePlan = Plan::getFreePlan();
        if ($freePlan) {
            return $freePlan->getLimitsArray();
        }

        return self::emergencyPlanLimits();
    }

    protected function hasDenormalizedLimits(): bool
    {
        return $this->max_patients !== null
            || $this->max_appointments_per_month !== null
            || $this->max_doctors !== null
            || $this->max_staff !== null
            || $this->max_storage_bytes !== null;
    }

    /**
     * Last-resort limits when the plans table has not been seeded.
     *
     * @return array<string, mixed>
     */
    public static function emergencyPlanLimits(): array
    {
        return [
            'max_patients' => 25,
            'max_appointments_per_month' => 5,
            'max_doctors' => 1,
            'max_staff' => 1,
            'max_storage_bytes' => 524288000,
            'features' => ['basic_forms', 'basic_portal'],
        ];
    }

    public function hasFeature(string $feature): bool
    {
        $limits = $this->getPlanLimits();

        return in_array($feature, $limits['features'] ?? []);
    }

    public function isCustomDomainVerified(): bool
    {
        return $this->custom_domain !== null
            && $this->custom_domain_verified_at !== null;
    }

    public function canAddPatient(): bool
    {
        if (! $this->canWrite()) {
            return false;
        }
        $limits = $this->getPlanLimits();
        if ($limits['max_patients'] === null) {
            return true;
        }

        return $this->patients()->count() < $limits['max_patients'];
    }

    public function canAddAppointmentThisMonth(): bool
    {
        if (! $this->canWrite()) {
            return false;
        }
        $limits = $this->getPlanLimits();
        if ($limits['max_appointments_per_month'] === null) {
            return true;
        }
        $count = $this->appointments()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $count < $limits['max_appointments_per_month'];
    }

    public function canAddDoctor(): bool
    {
        if (! $this->canWrite()) {
            return false;
        }
        $limits = $this->getPlanLimits();
        if ($limits['max_doctors'] === null) {
            return true;
        }

        // El owner cuenta como doctor a efectos del límite del plan
        $used = $this->practitioners()->count() + $this->pendingDoctorInvitationsCount();

        return $used < $limits['max_doctors'];
    }

    public function canAddStaff(): bool
    {
        if (! $this->canWrite()) {
            return false;
        }
        $limits = $this->getPlanLimits();
        if ($limits['max_staff'] === null) {
            return true;
        }

        return $this->staff()->count() + $this->pendingStaffInvitationsCount() < $limits['max_staff'];
    }

    public function pendingStaffInvitationsCount(): int
    {
        return $this->pendingInvitations()
            ->whereIn('role', ['assistant', 'secretary', 'receptionist'])
            ->count();
    }

    public function pendingDoctorInvitationsCount(): int
    {
        return $this->pendingInvitations()
            ->where('role', 'doctor')
            ->count();
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']) &&
               ($this->status !== 'trial' || $this->isOnTrial());
    }

    // ==================== ACCESS POLICY (ADR-008) ====================

    public const ACCESS_FULL = 'full';

    public const ACCESS_READ_ONLY = 'read_only';

    public const ACCESS_BILLING_ONLY = 'billing_only';

    /**
     * Nivel de acceso de la cuenta según ADR-008.
     *
     * - full         → lectura + escritura + portal público
     * - read_only    → lectura sí, escritura no, portal público bloqueado, billing accesible
     * - billing_only → ni lectura del panel; sólo billing (cuenta suspendida/cancelada)
     */
    public function accessLevel(): string
    {
        // Cuenta cerrada o suspendida: sólo billing accesible
        if (in_array($this->status, ['suspended', 'cancelled'], true)) {
            return self::ACCESS_BILLING_ONLY;
        }

        // Trial expirado: lectura sí, escritura no
        if ($this->status === 'trial' && $this->trial_ends_at?->isPast()) {
            return self::ACCESS_READ_ONLY;
        }

        // Free no-cortesía (auto-downgrade desde plan pagado caducado): read-only
        // Plan free de cortesía (asignado por admin con is_manual_plan=true) sí mantiene escritura
        if ($this->isOnFreePlan() && ! $this->is_manual_plan) {
            return self::ACCESS_READ_ONLY;
        }

        // active, trial vigente, plan free de cortesía, plan pagado activo → full
        return self::ACCESS_FULL;
    }

    public function canWrite(): bool
    {
        return $this->accessLevel() === self::ACCESS_FULL;
    }

    public function isReadOnly(): bool
    {
        return $this->accessLevel() === self::ACCESS_READ_ONLY;
    }

    public function isBillingOnly(): bool
    {
        return $this->accessLevel() === self::ACCESS_BILLING_ONLY;
    }

    /**
     * Si la cuenta puede acceder al panel de la app (lectura mínima).
     * billing_only NO puede acceder a la app, solo a /billing.
     */
    public function isAccessible(): bool
    {
        return $this->accessLevel() !== self::ACCESS_BILLING_ONLY;
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trial']);
    }

    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan_type', $plan);
    }

    // ==================== HELPERS ====================

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPublicUrl(): string
    {
        return url("/public/{$this->public_portal_slug}");
    }

    public function getDashboardUrl(): string
    {
        return url("/app/{$this->slug}");
    }

    // ==================== DATE / TIME FORMATTING ====================

    /**
     * PHP date format configured by the clinic (e.g. 'd/m/Y', 'Y-m-d', 'm/d/Y').
     */
    public function dateFormat(): string
    {
        return $this->settings['date_format'] ?? 'd/m/Y';
    }

    /**
     * Returns the PHP time format string according to the clinic's setting.
     */
    public function timeFormat(): string
    {
        return ($this->settings['time_format'] ?? '24h') === '12h' ? 'g:i A' : 'H:i';
    }

    /**
     * Format a date (or datetime) using the clinic's configured date format.
     * If $withTime is true, appends the time using the clinic's time format.
     * Returns '-' for null values.
     */
    public function formatDate(mixed $date, bool $withTime = false): string
    {
        if ($date === null) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $format = $withTime
            ? $this->dateFormat().' '.$this->timeFormat()
            : $this->dateFormat();

        return $carbon->format($format);
    }

    /**
     * Get default settings for new clinic
     */
    public static function getDefaultSettings(): array
    {
        return [
            'appointment_duration' => 30,
            'appointment_buffer' => 5,
            'working_days' => [1, 2, 3, 4, 5], // Mon-Fri
            'working_hours_start' => '08:00',
            'working_hours_end' => '18:00',
            'appointment_mode' => 'scheduled', // scheduled, walk_in, hybrid
            'walk_in_max_per_session' => 20,
            'allow_online_booking' => true,
            'require_booking_confirmation' => true,
            'send_reminders' => true,
            'reminder_hours_before' => 24,
            // Facturación
            'billing_enabled' => false,
            'invoice_prefix' => 'INV-',
            'next_invoice_number' => 1,
            'tax_rate' => 0,
            'tax_label' => 'IVA',
            'tax_included_in_price' => false,
            'default_consultation_price' => 0,
            'invoice_footer_text' => '',
        ];
    }

    public function billingEnabled(): bool
    {
        return (bool) ($this->settings['billing_enabled'] ?? false);
    }
}
