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

class Invoice extends Model
{
    use BelongsToClinic, HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    const STATUS_DRAFT = 'draft';

    const STATUS_PENDING = 'pending';

    const STATUS_PARTIAL = 'partial';

    const STATUS_PAID = 'paid';

    const STATUS_REFUNDED = 'refunded';

    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'created_by',
        'invoice_number',
        'issued_at',
        'due_at',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'currency',
        'notes',
        'is_demo',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'is_demo' => 'boolean',
        'due_at' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ──── Relaciones ────────────────────────────────────────────────────────

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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('paid_at');
    }

    // ──── Helpers ────────────────────────────────────────────────────────────

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PARTIAL => 'orange',
            self::STATUS_PAID => 'green',
            self::STATUS_REFUNDED => 'blue',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return __('invoices.status_'.$this->status);
    }
}
