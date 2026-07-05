<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    /**
     * Features shown on public pricing (v1 — exclude roadmap items like AI, SMS, API).
     *
     * @var list<string>
     */
    public const V1_FEATURE_KEYS = [
        'basic_forms',
        'basic_portal',
        'custom_portal',
        'multi_doctor_portal',
        'booking',
        'booking_advanced',
        'audit_logs',
        '2fa',
        'compliance',
    ];

    /**
     * Comparison rows for public pricing table (limits + v1 capabilities only).
     *
     * @return list<string>
     */
    public static function publicComparisonRows(): array
    {
        return [
            'row_doctors',
            'row_staff',
            'row_patients',
            'row_appointments',
            'row_medical_records',
            'row_prescriptions',
            'row_reports',
            'row_email_support',
        ];
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'max_patients',
        'max_appointments_per_month',
        'max_doctors',
        'max_staff',
        'max_storage_bytes',
        'features',
        'highlight_features',
        'monthly_price',
        'yearly_price',
        'paddle_monthly_price_id',
        'paddle_yearly_price_id',
        'paddle_product_id',
        'cta_text',
        'cta_url',
        'trial_days',
        'sort_order',
        'is_active',
        'is_popular',
        'is_free',
        'is_enterprise',
        'is_private',
        'requires_code',
        'access_code',
    ];

    protected $casts = [
        'features' => 'array',
        'highlight_features' => 'array',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_patients' => 'integer',
        'max_appointments_per_month' => 'integer',
        'max_doctors' => 'integer',
        'max_staff' => 'integer',
        'max_storage_bytes' => 'integer',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_free' => 'boolean',
        'is_enterprise' => 'boolean',
        'is_private' => 'boolean',
        'requires_code' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }

    /**
     * Slugs that must never be deleted from the admin panel.
     *
     * @var list<string>
     */
    public const PROTECTED_SLUGS = ['free'];

    public function isProtected(): bool
    {
        return in_array($this->slug, self::PROTECTED_SLUGS, true);
    }

    public function canBeDeleted(): bool
    {
        if ($this->isProtected()) {
            return false;
        }

        return $this->clinics()->count() === 0;
    }

    public function deletionBlockReason(): ?string
    {
        if ($this->isProtected()) {
            return __('admin.plan_delete_protected');
        }

        $count = $this->clinics()->count();
        if ($count > 0) {
            return __('admin.plan_delete_has_clinics', ['count' => $count]);
        }

        return null;
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeSubscribable($query)
    {
        return $query->where('is_free', false)->where('is_enterprise', false)->where('is_active', true);
    }

    /**
     * Public plans visible on the marketing /pricing page.
     * Excludes plans flagged as private (used internally or by partners only).
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Plans shown on the in-app billing page for a clinic.
     */
    public function scopeBillingVisibleFor($query, Clinic $clinic)
    {
        $unlocked = $clinic->unlockedPlanSlugs();

        return $query->active()
            ->where('is_free', false)
            ->where(function ($q) use ($unlocked) {
                $q->where('is_private', false)
                    ->orWhereIn('slug', $unlocked);
            });
    }

    /**
     * Plans on the public /pricing page (excludes enterprise until custom quoting exists).
     */
    public function scopeForPublicPricing($query)
    {
        return $query->active()->public()->where('is_enterprise', false);
    }

    public static function findByAccessCode(string $code): ?self
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        return static::query()
            ->where('requires_code', true)
            ->where('is_active', true)
            ->whereNotNull('access_code')
            ->whereRaw('UPPER(access_code) = ?', [$normalized])
            ->first();
    }

    // ==================== HELPERS ====================

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function isUnlimitedPatients(): bool
    {
        return $this->max_patients === null;
    }

    public function isUnlimitedAppointments(): bool
    {
        return $this->max_appointments_per_month === null;
    }

    public function getLimitsArray(): array
    {
        return [
            'max_patients' => $this->max_patients,
            'max_appointments_per_month' => $this->max_appointments_per_month,
            'max_doctors' => $this->max_doctors,
            'max_staff' => $this->max_staff,
            'max_storage_bytes' => $this->max_storage_bytes,
            'features' => $this->features ?? [],
        ];
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getFreePlan(): ?self
    {
        return static::where('is_free', true)->first();
    }

    /**
     * Resolve a plan from a Paddle price ID (monthly or yearly on the same product).
     */
    public static function findByPaddlePriceId(string $priceId): ?self
    {
        return static::query()
            ->where('paddle_monthly_price_id', $priceId)
            ->orWhere('paddle_yearly_price_id', $priceId)
            ->first();
    }

    public function paddlePriceIdForCycle(string $cycle): ?string
    {
        return $cycle === 'yearly'
            ? $this->paddle_yearly_price_id
            : $this->paddle_monthly_price_id;
    }

    /**
     * Denormalized limit columns to copy onto a clinic row.
     *
     * @return array<string, int|null>
     */
    public function limitsForClinicColumns(): array
    {
        return [
            'max_patients' => $this->max_patients,
            'max_appointments_per_month' => $this->max_appointments_per_month,
            'max_doctors' => $this->max_doctors,
            'max_staff' => $this->max_staff,
            'max_storage_bytes' => $this->max_storage_bytes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function registrationAttributesFrom(?self $plan): array
    {
        $plan ??= static::getFreePlan();

        if (! $plan) {
            return [
                'plan_id' => null,
                'plan_type' => 'free',
            ];
        }

        return array_merge(
            [
                'plan_id' => $plan->id,
                'plan_type' => $plan->slug,
            ],
            $plan->limitsForClinicColumns()
        );
    }

    /**
     * Get translated description for the plan.
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return __("features.{$this->slug}_description");
    }

    /**
     * Get the total number of users included in this plan.
     */
    public function getTotalUsersAttribute(): ?int
    {
        if ($this->max_doctors === null && $this->max_staff === null) {
            return null; // unlimited
        }

        return ($this->max_doctors ?? 0) + ($this->max_staff ?? 0);
    }

    /**
     * Get the monthly price for yearly billing (per month).
     */
    public function getYearlyMonthlyPriceAttribute(): ?float
    {
        if ($this->yearly_price === null) {
            return null;
        }

        return round((float) $this->yearly_price / 12, 0);
    }

    /**
     * Get the annual savings amount.
     */
    public function getAnnualSavingsAttribute(): ?float
    {
        if ($this->monthly_price === null || $this->yearly_price === null) {
            return null;
        }

        return ((float) $this->monthly_price * 12) - (float) $this->yearly_price;
    }

    /**
     * Generate display features from DB limits + v1 feature keys (or highlight_features override).
     *
     * @return list<string>
     */
    public function getDisplayFeaturesAttribute(): array
    {
        if (! empty($this->highlight_features)) {
            return collect($this->highlight_features)
                ->map(fn ($key) => $this->translateFeatureKey((string) $key))
                ->filter()
                ->values()
                ->all();
        }

        $items = [];

        if ($this->max_doctors === null) {
            $items[] = __('features.doctors_unlimited');
        } elseif ($this->max_doctors > 0) {
            $items[] = trans_choice('features.doctors_count', $this->max_doctors, ['count' => $this->max_doctors]);
        }

        if ($this->max_staff === null) {
            $items[] = __('features.staff_unlimited');
        } elseif ($this->max_staff > 0) {
            $items[] = trans_choice('features.staff_count', $this->max_staff, ['count' => $this->max_staff]);
        }

        if ($this->max_patients === null) {
            $items[] = __('features.patients_unlimited');
        } else {
            $items[] = __('features.patients_count', ['count' => $this->max_patients]);
        }

        if ($this->max_appointments_per_month === null) {
            $items[] = __('features.appointments_unlimited');
        } else {
            $items[] = __('features.appointments_count', ['count' => $this->max_appointments_per_month]);
        }

        foreach ($this->v1FeatureKeys() as $featureKey) {
            $translated = $this->translateFeatureKey($featureKey);
            if ($translated) {
                $items[] = $translated;
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    public function v1FeatureKeys(): array
    {
        return array_values(array_intersect($this->features ?? [], self::V1_FEATURE_KEYS));
    }

    protected function translateFeatureKey(string $featureKey): ?string
    {
        if (! in_array($featureKey, self::V1_FEATURE_KEYS, true)) {
            return null;
        }

        $translated = __("features.{$featureKey}");

        return $translated !== "features.{$featureKey}" ? $translated : null;
    }

    /**
     * Get the value for a comparison row.
     * Returns a string value or boolean for check/cross display.
     */
    public function getComparisonValue(string $row): string|bool
    {
        return match ($row) {
            'row_doctors' => $this->max_doctors === null
                ? __('features.doctors_unlimited')
                : (string) $this->max_doctors,
            'row_staff' => $this->max_staff === null
                ? __('features.staff_unlimited')
                : (string) $this->max_staff,
            'row_patients' => $this->max_patients === null
                ? __('features.patients_unlimited')
                : (string) $this->max_patients,
            'row_appointments' => $this->max_appointments_per_month === null
                ? __('features.appointments_unlimited')
                : (string) $this->max_appointments_per_month,
            'row_medical_records' => true,
            'row_prescriptions' => ! $this->is_free,
            'row_reports' => true,
            'row_email_support' => true,
            // Legacy rows (kept for backwards compatibility if referenced elsewhere)
            'row_users' => $this->total_users === null
                ? __('features.users_unlimited')
                : (string) $this->total_users,
            'row_email_reminders' => $this->hasFeature('booking') || $this->hasFeature('booking_advanced'),
            'row_sms_reminders' => false,
            'row_whatsapp_reminders' => false,
            'row_booking' => $this->hasFeature('booking') || $this->hasFeature('booking_advanced'),
            'row_basic_reports' => true,
            'row_advanced_reports' => $this->hasFeature('audit_logs'),
            'row_custom_branding' => $this->hasFeature('custom_portal') || $this->hasFeature('multi_doctor_portal'),
            'row_api' => false,
            'row_white_label' => false,
            'row_priority_support' => false,
            'row_24_7_support' => false,
            default => false,
        };
    }
}
