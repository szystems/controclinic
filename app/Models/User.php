<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    // Role constants
    public const ROLE_OWNER = 'owner';

    public const ROLE_DOCTOR = 'doctor';

    public const ROLE_ASSISTANT = 'assistant';

    public const ROLE_SECRETARY = 'secretary';

    public const ROLE_RECEPTIONIST = 'receptionist';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'clinic_id',
        'role',
        'phone',
        'avatar',
        'locale',
        'timezone',
        'theme',
        'specialties',
        'bio',
        'license_number',
        'working_hours',
        'is_active',
        'is_super_admin',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'specialties' => 'array',
            'working_hours' => 'array',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ==================== ACTIVITY LOG ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ==================== RELATIONSHIPS ====================

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'primary_doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id');
    }

    // ==================== ACCESSORS ====================

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return $initials;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => __('Propietario'),
            self::ROLE_DOCTOR => __('Doctor'),
            self::ROLE_ASSISTANT => __('Asistente'),
            self::ROLE_SECRETARY => __('Secretaria'),
            self::ROLE_RECEPTIONIST => __('Recepcionista'),
            self::ROLE_ADMIN => __('Administrador'),
            default => $this->role,
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0D8ABC&color=fff';
    }

    // ==================== ROLE CHECKS ====================

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isDoctor(): bool
    {
        return $this->role === self::ROLE_DOCTOR;
    }

    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    public function isSecretary(): bool
    {
        return $this->role === self::ROLE_SECRETARY;
    }

    public function isReceptionist(): bool
    {
        return $this->role === self::ROLE_RECEPTIONIST;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_ASSISTANT,
            self::ROLE_SECRETARY,
            self::ROLE_RECEPTIONIST,
        ]);
    }

    public function canManageClinic(): bool
    {
        return in_array($this->role, [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
        ]);
    }

    public function canViewMedicalRecords(): bool
    {
        return in_array($this->role, [
            self::ROLE_OWNER,
            self::ROLE_DOCTOR,
            self::ROLE_ADMIN,
        ]);
    }

    public function canManageAppointments(): bool
    {
        return in_array($this->role, [
            self::ROLE_OWNER,
            self::ROLE_DOCTOR,
            self::ROLE_ASSISTANT,
            self::ROLE_SECRETARY,
            self::ROLE_RECEPTIONIST,
            self::ROLE_ADMIN,
        ]);
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

    public function scopeDoctors($query)
    {
        return $query->where('role', self::ROLE_DOCTOR);
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('role', [
            self::ROLE_ASSISTANT,
            self::ROLE_SECRETARY,
            self::ROLE_RECEPTIONIST,
        ]);
    }

    // ==================== METHODS ====================

    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function getTodayAppointments()
    {
        return $this->appointments()
            ->today()
            ->orderBy('start_time')
            ->get();
    }

    public function getUpcomingAppointments(int $limit = 10)
    {
        return $this->appointments()
            ->upcoming()
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Get default working hours
     */
    public static function getDefaultWorkingHours(): array
    {
        return [
            'monday' => ['start' => '08:00', 'end' => '17:00', 'enabled' => true],
            'tuesday' => ['start' => '08:00', 'end' => '17:00', 'enabled' => true],
            'wednesday' => ['start' => '08:00', 'end' => '17:00', 'enabled' => true],
            'thursday' => ['start' => '08:00', 'end' => '17:00', 'enabled' => true],
            'friday' => ['start' => '08:00', 'end' => '17:00', 'enabled' => true],
            'saturday' => ['start' => '08:00', 'end' => '12:00', 'enabled' => false],
            'sunday' => ['start' => null, 'end' => null, 'enabled' => false],
        ];
    }
}
