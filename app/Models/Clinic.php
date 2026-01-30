<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Paddle\Billable;

class Clinic extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Billable;

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
        'plan_type',
        'status',
        'trial_ends_at',
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
        'trial_ends_at' => 'datetime',
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

    // ==================== PLAN & LIMITS ====================

    public function getPlanLimits(): array
    {
        return self::PLAN_LIMITS[$this->plan_type] ?? self::PLAN_LIMITS['free'];
    }

    public function hasFeature(string $feature): bool
    {
        $limits = $this->getPlanLimits();
        return in_array($feature, $limits['features'] ?? []);
    }

    public function canAddPatient(): bool
    {
        $limits = $this->getPlanLimits();
        if ($limits['max_patients'] === null) {
            return true;
        }
        return $this->patients()->count() < $limits['max_patients'];
    }

    public function canAddAppointmentThisMonth(): bool
    {
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
        $limits = $this->getPlanLimits();
        if ($limits['max_doctors'] === null) {
            return true;
        }
        return $this->doctors()->count() < $limits['max_doctors'];
    }

    public function canAddStaff(): bool
    {
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
