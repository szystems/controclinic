<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClinicInvitation extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'clinic_id',
        'email',
        'name',
        'role',
        'token',
        'invited_by',
        'expires_at',
        'accepted_at',
        'cancelled_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at)
            && is_null($this->cancelled_at)
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return is_null($this->accepted_at)
            && is_null($this->cancelled_at)
            && $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public function isCancelled(): bool
    {
        return ! is_null($this->cancelled_at);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
            ->whereNull('cancelled_at')
            ->where('expires_at', '>', now());
    }

    public function scopeForClinic($query, string $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
