<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Prescription extends Model
{
    use BelongsToClinic, HasUuids, LogsActivity, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'status',
        'issued_at',
        'valid_until',
        'diagnosis',
        'notes',
        'internal_notes',
        'qr_payload',
        'signature_path',
        'folio',
        'is_demo',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'is_demo' => 'boolean',
        'valid_until' => 'date',
        'is_controlled' => 'boolean',
    ];

    // ==================== CONSTANTES ====================

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_DISPENSED = 'dispensed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ISSUED,
        self::STATUS_DISPENSED,
        self::STATUS_CANCELLED,
    ];

    // ==================== ACTIVITY LOG ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'folio', 'issued_at', 'valid_until'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ==================== RELACIONES ====================

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

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('order');
    }

    // ==================== ACCESORS ====================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('prescriptions.status_draft'),
            self::STATUS_ISSUED => __('prescriptions.status_issued'),
            self::STATUS_DISPENSED => __('prescriptions.status_dispensed'),
            self::STATUS_CANCELLED => __('prescriptions.status_cancelled'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_ISSUED => 'blue',
            self::STATUS_DISPENSED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        if (! $this->valid_until) {
            return false;
        }

        $today = $this->clinic?->localNow()->toDateString()
            ?? now()->timezone(config('app.timezone'))->toDateString();

        return $this->valid_until->toDateString() < $today;
    }

    // ==================== SCOPES ====================

    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    // ==================== MÉTODOS ====================

    /**
     * Genera el folio siguiente para la clínica: RX-0001, RX-0002, etc.
     */
    public static function generateFolio(string $clinicId): string
    {
        return DB::transaction(function () use ($clinicId) {
            Clinic::whereKey($clinicId)->lockForUpdate()->first();

            return static::nextFolio($clinicId);
        });
    }

    private static function nextFolio(string $clinicId): string
    {
        $order = DB::connection()->getDriverName() === 'sqlite'
            ? 'CAST(SUBSTR(folio, ?) AS INTEGER) DESC'
            : 'CAST(SUBSTRING(folio, ?) AS UNSIGNED) DESC';

        $last = static::withTrashed()
            ->where('clinic_id', $clinicId)
            ->whereNotNull('folio')
            ->orderByRaw($order, [4])
            ->value('folio');

        if (! $last) {
            return 'RX-0001';
        }

        $number = (int) preg_replace('/\D/', '', $last);

        return 'RX-'.str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Genera el QR payload (hash único firmado con APP_KEY).
     */
    public static function generateQrPayload(string $id): string
    {
        return hash_hmac('sha256', $id, config('app.key'));
    }

    public function issue(): void
    {
        DB::transaction(function () {
            Clinic::whereKey($this->clinic_id)->lockForUpdate()->first();

            $this->update([
                'status' => self::STATUS_ISSUED,
                'issued_at' => $this->issued_at ?? $this->clinic->localNow()->toDateString(),
                'folio' => $this->folio ?? static::nextFolio($this->clinic_id),
                'qr_payload' => $this->qr_payload ?? static::generateQrPayload($this->id),
            ]);
        });
    }

    public function cancel(): void
    {
        if (! in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ISSUED], true)) {
            return;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function markDispensed(): void
    {
        if ($this->status !== self::STATUS_ISSUED) {
            return;
        }

        $this->update(['status' => self::STATUS_DISPENSED]);
    }
}
