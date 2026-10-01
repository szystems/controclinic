<?php

namespace App\Models;

use App\Jobs\SendAppointmentNotification;
use App\Traits\BelongsToClinic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use BelongsToClinic, HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (self $appointment) {
            if (empty($appointment->confirmation_token)) {
                $appointment->confirmation_token = Str::random(64);
            }
        });
    }

    public $incrementing = false;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'appointment_type',
        'appointment_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'queue_number',
        'queue_period',
        'status',
        'reason',
        'symptoms',
        'notes',
        'checked_in_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'reminder_sent',
        'reminder_sent_at',
        'room',
        'resources',
        'is_recurring',
        'recurring_pattern_id',
        'branch_id',
        'consultation_price',
        'consultation_discount',
        'is_billable',
        'confirmation_token',
        'confirmed_via',
        'telemedicine_link',
        'telemedicine_provider',
        'pre_consultation_form_id',
        'parent_appointment_id',
        'created_via',
        'nps_sent_at',
        'is_demo',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'is_demo' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'queue_number' => 'integer',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'resources' => 'array',
        'is_recurring' => 'boolean',
        'consultation_price' => 'decimal:2',
        'consultation_discount' => 'decimal:2',
        'is_billable' => 'boolean',
        'nps_sent_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    // Type constants
    public const TYPE_SCHEDULED = 'scheduled';

    public const TYPE_WALK_IN = 'walk_in';

    public const TYPE_EMERGENCY = 'emergency';

    public const TYPE_FOLLOW_UP = 'follow_up';

    public const TYPE_TELEMEDICINE = 'telemedicine';

    // ==================== ACTIVITY LOG ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'appointment_date',
                'start_time',
                'end_time',
                'doctor_id',
                'patient_id',
                'appointment_type',
                'cancellation_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ==================== RELATIONSHIPS ====================

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AppointmentComment::class)->orderBy('created_at');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderByDesc('created_at');
    }

    // ==================== ACCESSORS ====================

    public function getStartDateTimeAttribute(): ?Carbon
    {
        if (! $this->start_time) {
            return null;
        }

        return Carbon::parse($this->appointment_date->format('Y-m-d').' '.$this->start_time);
    }

    public function getEndDateTimeAttribute(): ?Carbon
    {
        if (! $this->end_time) {
            return $this->start_date_time?->addMinutes($this->duration_minutes);
        }

        return Carbon::parse($this->appointment_date->format('Y-m-d').' '.$this->end_time);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'blue',
            self::STATUS_CONFIRMED => 'indigo',
            self::STATUS_WAITING => 'yellow',
            self::STATUS_IN_PROGRESS => 'green',
            self::STATUS_COMPLETED => 'gray',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_NO_SHOW => 'orange',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => __('appointments.status_scheduled'),
            self::STATUS_CONFIRMED => __('appointments.status_confirmed'),
            self::STATUS_WAITING => __('appointments.status_waiting'),
            self::STATUS_IN_PROGRESS => __('appointments.status_in_progress'),
            self::STATUS_COMPLETED => __('appointments.status_completed'),
            self::STATUS_CANCELLED => __('appointments.status_cancelled'),
            self::STATUS_NO_SHOW => __('appointments.status_no_show'),
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->appointment_type) {
            self::TYPE_SCHEDULED => __('appointments.scheduled'),
            self::TYPE_WALK_IN => __('appointments.walk_in'),
            self::TYPE_EMERGENCY => __('appointments.emergency'),
            self::TYPE_FOLLOW_UP => __('appointments.follow_up'),
            self::TYPE_TELEMEDICINE => __('appointments.telemedicine'),
            default => $this->appointment_type,
        };
    }

    // ==================== SCOPES ====================

    public function scopeForClinic($query, string $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    public static function clinicToday(): string
    {
        $clinic = app()->bound('current_clinic') ? app('current_clinic') : null;
        $timezone = $clinic?->timezone ?: config('app.timezone');

        return now()->copy()->timezone($timezone)->toDateString();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', static::clinicToday())
            ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_CONFIRMED]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', static::clinicToday());
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SCHEDULED,
            self::STATUS_CONFIRMED,
            self::STATUS_WAITING,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
            self::STATUS_NO_SHOW,
        ]);
    }

    // ==================== METHODS ====================

    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_SCHEDULED,
            self::STATUS_CONFIRMED,
        ]);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_SCHEDULED,
            self::STATUS_CONFIRMED,
            self::STATUS_WAITING,
        ]);
    }

    public function canCheckIn(): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && $this->appointment_date->isToday();
    }

    public function canStart(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function checkIn(): void
    {
        $this->update([
            'status' => self::STATUS_WAITING,
            'checked_in_at' => now(),
        ]);
    }

    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        // Actualizar última visita del paciente
        $this->patient->updateLastVisit();
    }

    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        SendAppointmentNotification::dispatch(
            $this->id,
            SendAppointmentNotification::TYPE_CANCELLED,
        );
    }

    public function markAsNoShow(): void
    {
        $this->update([
            'status' => self::STATUS_NO_SHOW,
        ]);
    }

    public function confirm(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
        ]);

        SendAppointmentNotification::dispatch(
            $this->id,
            SendAppointmentNotification::TYPE_CONFIRMED,
        );
    }

    /**
     * Calculate end time based on start time and duration
     */
    public function calculateEndTime(): string
    {
        if (! $this->start_time) {
            return '';
        }

        return Carbon::parse($this->start_time)
            ->addMinutes($this->duration_minutes)
            ->format('H:i');
    }

    /**
     * Check if appointment conflicts with another
     */
    public function conflictsWith(Appointment $other): bool
    {
        if ($this->doctor_id !== $other->doctor_id) {
            return false;
        }

        if (! $this->appointment_date->isSameDay($other->appointment_date)) {
            return false;
        }

        $thisStart = $this->start_date_time;
        $thisEnd = $this->end_date_time;
        $otherStart = $other->start_date_time;
        $otherEnd = $other->end_date_time;

        return $thisStart < $otherEnd && $thisEnd > $otherStart;
    }
}
