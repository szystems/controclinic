<?php

namespace App\Models;

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
        'max_patients',
        'max_appointments_per_month',
        'max_doctors',
        'max_staff',
        'storage_used_bytes',
        'max_storage_bytes',
    ];

    protected $casts = [
        'settings' => 'array',
        'branding' => 'array',
        'public_portal_enabled' => 'boolean',
        'is_manual_plan' => 'boolean',
        'trial_ends_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'max_patients' => 'integer',
        'max_appointments_per_month' => 'integer',
        'max_doctors' => 'integer',
        'max_staff' => 'integer',
        'storage_used_bytes' => 'integer',
        'max_storage_bytes' => 'integer',
    ];

    /**
     * Plan limits configuration
     */
    public const PLAN_LIMITS = [
        'free' => [
            'max_patients' => 25,
            'max_appointments_per_month' => 5,
            'max_doctors' => 1,
            'max_staff' => 0,
            'max_storage_bytes' => 524288000, // 500MB
            'features' => ['basic_forms', 'basic_portal'],
        ],
        'solo' => [
            'max_patients' => null, // Unlimited
            'max_appointments_per_month' => null,
            'max_doctors' => 1,
            'max_staff' => 1,
            'max_storage_bytes' => null, // Fair use
            'features' => ['ai', 'mobile_basic', '2fa', 'compliance', 'custom_portal', 'booking'],
        ],
        'group' => [
            'max_patients' => null,
            'max_appointments_per_month' => null,
            'max_doctors' => 5,
            'max_staff' => 3,
            'max_storage_bytes' => null,
            'features' => ['ai', 'ai_collaborative', 'mobile_advanced', '2fa', 'compliance', 'audit_logs', 'multi_doctor_portal', 'booking_advanced'],
        ],
        'enterprise' => [
            'max_patients' => null,
            'max_appointments_per_month' => null,
            'max_doctors' => null,
            'max_staff' => null,
            'max_storage_bytes' => null,
            'features' => ['ai', 'ai_custom', 'mobile_enterprise', '2fa', 'compliance', 'audit_logs', 'api', 'white_label', 'bi', 'custom_domain'],
        ],
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

    public function getPlanLimits(): array
    {
        // Prefer Plan model from DB, fallback to constants
        if ($this->plan_id && $this->relationLoaded('plan') ? $this->plan : $this->plan()->exists()) {
            return $this->plan->getLimitsArray();
        }

        return self::PLAN_LIMITS[$this->plan_type] ?? self::PLAN_LIMITS['free'];
    }

    public function hasFeature(string $feature): bool
    {
        $limits = $this->getPlanLimits();

        return in_array($feature, $limits['features'] ?? []);
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

        return $this->doctors()->count() < $limits['max_doctors'];
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

        return $this->staff()->count() < $limits['max_staff'];
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
        if ($this->plan_type === 'free' && ! $this->is_manual_plan) {
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
        ];
    }
}
