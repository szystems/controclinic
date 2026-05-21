<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalRecord extends Model
{
    use BelongsToClinic, HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'record_type',
        'title',
        'content',
        'chief_complaint',
        'present_illness',
        'physical_examination',
        'assessment',
        'plan',
        'vital_signs',
        'diagnoses',
        'prescriptions',
        'attachments',
        'is_confidential',
        'visible_to_roles',
        'status',
        'finalized_at',
        'amendment_of_id',
        'template_id',
        'signed_at',
        'signature_hash',
        'ai_generated',
        'ai_metadata',
        'qr_payload',
    ];

    protected $casts = [
        'vital_signs' => 'array',
        'diagnoses' => 'array',
        'prescriptions' => 'array',
        'attachments' => 'array',
        'visible_to_roles' => 'array',
        'is_confidential' => 'boolean',
        'finalized_at' => 'datetime',
        'signed_at' => 'datetime',
        'ai_generated' => 'boolean',
        'ai_metadata' => 'array',
    ];

    // Record types
    public const TYPE_CONSULTATION = 'consultation';

    public const TYPE_DIAGNOSIS = 'diagnosis';

    public const TYPE_PRESCRIPTION = 'prescription';

    public const TYPE_LAB_RESULT = 'lab_result';

    public const TYPE_IMAGING = 'imaging';

    public const TYPE_PROCEDURE = 'procedure';

    public const TYPE_SURGERY = 'surgery';

    public const TYPE_REFERRAL = 'referral';

    public const TYPE_FOLLOW_UP_NOTE = 'follow_up_note';

    public const TYPE_VITAL_SIGNS = 'vital_signs';

    public const TYPE_VACCINATION = 'vaccination';

    public const TYPE_OTHER = 'other';

    // Status
    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINAL = 'final';

    public const STATUS_AMENDED = 'amended';

    public const STATUS_DELETED = 'deleted';

    // ==================== ACTIVITY LOG ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['record_type', 'status', 'title'])
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
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // ==================== ACCESSORS ====================

    public function getTypeLabelAttribute(): string
    {
        return match ($this->record_type) {
            self::TYPE_CONSULTATION => __('Consulta'),
            self::TYPE_DIAGNOSIS => __('Diagnóstico'),
            self::TYPE_PRESCRIPTION => __('Receta'),
            self::TYPE_LAB_RESULT => __('Resultado de laboratorio'),
            self::TYPE_IMAGING => __('Imagenología'),
            self::TYPE_PROCEDURE => __('Procedimiento'),
            self::TYPE_SURGERY => __('Cirugía'),
            self::TYPE_REFERRAL => __('Referencia'),
            self::TYPE_FOLLOW_UP_NOTE => __('Nota de seguimiento'),
            self::TYPE_VITAL_SIGNS => __('Signos vitales'),
            self::TYPE_VACCINATION => __('Vacunación'),
            self::TYPE_OTHER => __('Otro'),
            default => $this->record_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('Borrador'),
            self::STATUS_FINAL => __('Finalizado'),
            self::STATUS_AMENDED => __('Modificado'),
            self::STATUS_DELETED => __('Eliminado'),
            default => $this->status,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->record_type) {
            self::TYPE_CONSULTATION => 'clipboard-document-list',
            self::TYPE_DIAGNOSIS => 'magnifying-glass',
            self::TYPE_PRESCRIPTION => 'document-text',
            self::TYPE_LAB_RESULT => 'beaker',
            self::TYPE_IMAGING => 'photo',
            self::TYPE_PROCEDURE => 'wrench',
            self::TYPE_SURGERY => 'scissors',
            self::TYPE_REFERRAL => 'arrow-right',
            self::TYPE_FOLLOW_UP_NOTE => 'chat-bubble-left-right',
            self::TYPE_VITAL_SIGNS => 'heart',
            self::TYPE_VACCINATION => 'shield-check',
            default => 'document',
        };
    }

    // ==================== SCOPES ====================

    public function scopeForClinic($query, string $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('record_type', $type);
    }

    public function scopeConsultations($query)
    {
        return $query->where('record_type', self::TYPE_CONSULTATION);
    }

    public function scopePrescriptions($query)
    {
        return $query->where('record_type', self::TYPE_PRESCRIPTION);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeNotConfidential($query)
    {
        return $query->where('is_confidential', false);
    }

    public function scopeVisibleToRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('visible_to_roles')
                ->orWhereJsonContains('visible_to_roles', $role);
        });
    }

    // ==================== METHODS ====================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function finalize(): void
    {
        $this->update([
            'status' => self::STATUS_FINAL,
            'finalized_at' => now(),
        ]);
    }

    public function amend(): void
    {
        $this->update([
            'status' => self::STATUS_AMENDED,
        ]);
    }

    public function canBeViewedBy(User $user): bool
    {
        // El doctor que creó siempre puede ver
        if ($user->id === $this->doctor_id) {
            return true;
        }

        // Si es confidencial, solo el creador puede ver
        if ($this->is_confidential) {
            return false;
        }

        // Si hay restricción de roles
        if (! empty($this->visible_to_roles)) {
            return in_array($user->role, $this->visible_to_roles);
        }

        // Por defecto, doctores y owners pueden ver
        return in_array($user->role, ['owner', 'doctor', 'admin']);
    }

    /**
     * Get vital signs with labels
     */
    public function getFormattedVitalSigns(): array
    {
        $signs = $this->vital_signs ?? [];
        $labels = [
            'blood_pressure_systolic' => __('Presión sistólica'),
            'blood_pressure_diastolic' => __('Presión diastólica'),
            'heart_rate' => __('Frecuencia cardíaca'),
            'temperature' => __('Temperatura'),
            'respiratory_rate' => __('Frecuencia respiratoria'),
            'oxygen_saturation' => __('Saturación O2'),
            'weight' => __('Peso'),
            'height' => __('Altura'),
        ];

        $formatted = [];
        foreach ($signs as $key => $value) {
            if (isset($labels[$key]) && $value !== null) {
                $formatted[$key] = [
                    'label' => $labels[$key],
                    'value' => $value,
                ];
            }
        }

        return $formatted;
    }

    /**
     * Template structure for SOAP note
     */
    public static function soapTemplate(): array
    {
        return [
            'chief_complaint' => '',
            'present_illness' => '',
            'physical_examination' => '',
            'assessment' => '',
            'plan' => '',
        ];
    }
}
