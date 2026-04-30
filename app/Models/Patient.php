<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use BelongsToClinic, HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'clinic_id',
        'primary_doctor_id',
        'medical_record_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_secondary',
        'birth_date',
        'gender',
        'id_type',
        'id_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'blood_type',
        'allergies',
        'chronic_conditions',
        'current_medications',
        'emergency_contacts',
        'insurance_info',
        'notes',
        'preferences',
        'is_active',
        'last_visit_at',
        'internal_notes',
        'portal_user_id',
        'external_id',
        'consent_signed_at',
        'marketing_opt_in',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'emergency_contacts' => 'array',
        'insurance_info' => 'array',
        'preferences' => 'array',
        'is_active' => 'boolean',
        'last_visit_at' => 'datetime',
        'consent_signed_at' => 'datetime',
        'marketing_opt_in' => 'boolean',
    ];

    // ==================== ACTIVITY LOG ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'phone', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ==================== RELATIONSHIPS ====================

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function primaryDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    // ==================== ACCESSORS ====================

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(
            substr($this->first_name, 0, 1).substr($this->last_name, 0, 1)
        );
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClinic($query, string $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('primary_doctor_id', $doctorId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('medical_record_number', 'like', "%{$search}%");
        });
    }

    // ==================== METHODS ====================

    public function generateMedicalRecordNumber(): string
    {
        $prefix = strtoupper(substr($this->clinic->slug, 0, 3));
        $year = now()->format('y');
        $sequence = $this->clinic->patients()->count() + 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }

    public function updateLastVisit(): void
    {
        $this->update(['last_visit_at' => now()]);
    }

    public function hasAllergies(): bool
    {
        return ! empty($this->allergies);
    }

    public function hasChronicConditions(): bool
    {
        return ! empty($this->chronic_conditions);
    }

    public function getUpcomingAppointments(int $limit = 5)
    {
        return $this->appointments()
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function getRecentMedicalRecords(int $limit = 10)
    {
        return $this->medicalRecords()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
