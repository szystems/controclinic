<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

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
     * Generate display features list from plan limits + feature keys.
     * Returns translated strings ready for display.
     */
    public function getDisplayFeaturesAttribute(): array
    {
        $items = [];

        // Users
        $totalUsers = $this->total_users;
        if ($totalUsers === null) {
            $items[] = __('features.users_unlimited');
        } else {
            $items[] = trans_choice('features.users_count', $totalUsers);
        }

        // Patients
        if ($this->max_patients === null) {
            $items[] = __('features.patients_unlimited');
        } else {
            $items[] = __('features.patients_count', ['count' => $this->max_patients]);
        }

        // Appointments
        if ($this->max_appointments_per_month === null) {
            $items[] = __('features.appointments_unlimited');
        } else {
            $items[] = __('features.appointments_count', ['count' => $this->max_appointments_per_month]);
        }

        // Feature keys from JSON
        foreach ($this->features ?? [] as $featureKey) {
            $translated = __("features.{$featureKey}");
            // Only add if translation exists (not the key itself)
            if ($translated !== "features.{$featureKey}") {
                $items[] = $translated;
            }
        }

        return $items;
    }

    /**
     * Get the value for a comparison row.
     * Returns a string value or boolean for check/cross display.
     */
    public function getComparisonValue(string $row): string|bool
    {
        return match ($row) {
            'row_users' => $this->total_users === null
                ? __('features.users_unlimited')
                : (string) $this->total_users,
            'row_patients' => $this->max_patients === null
                ? __('features.patients_unlimited')
                : (string) $this->max_patients,
            'row_appointments' => $this->max_appointments_per_month === null
                ? __('features.appointments_unlimited')
                : (string) $this->max_appointments_per_month,
            'row_email_reminders' => $this->hasFeature('booking') || $this->hasFeature('booking_advanced') || $this->is_enterprise,
            'row_sms_reminders' => $this->hasFeature('booking_advanced') || $this->is_enterprise,
            'row_whatsapp_reminders' => $this->is_enterprise,
            'row_booking' => $this->hasFeature('booking') || $this->hasFeature('booking_advanced'),
            'row_basic_reports' => $this->hasFeature('booking') || $this->hasFeature('booking_advanced') || $this->is_enterprise,
            'row_advanced_reports' => $this->hasFeature('audit_logs') || $this->is_enterprise,
            'row_custom_branding' => $this->hasFeature('multi_doctor_portal') || $this->is_enterprise,
            'row_api' => $this->hasFeature('api'),
            'row_white_label' => $this->hasFeature('white_label'),
            'row_email_support' => true,
            'row_priority_support' => $this->hasFeature('audit_logs') || $this->is_enterprise,
            'row_24_7_support' => $this->is_enterprise,
            default => false,
        };
    }
}
