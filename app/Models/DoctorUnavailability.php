<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorUnavailability extends Model
{
    use BelongsToClinic, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'date_from',
        'date_to',
        'all_day',
        'time_from',
        'time_to',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'all_day' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== SCOPES ====================

    /**
     * Unavailabilities that overlap with a given date range.
     */
    public function scopeOverlapping($query, string $dateFrom, string $dateTo)
    {
        return $query->whereDate('date_from', '<=', $dateTo)
            ->whereDate('date_to', '>=', $dateFrom);
    }

    /**
     * Unavailabilities that cover a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    // ==================== HELPERS ====================

    /**
     * Whether this unavailability covers the given date entirely (all_day)
     * or, if partial, whether the time slot overlaps.
     */
    public function blocksSlot(string $date, string $startTime, string $endTime): bool
    {
        if (! $this->coversDate($date)) {
            return false;
        }

        if ($this->all_day) {
            return true;
        }

        $slotStart = Carbon::parse($date.' '.$startTime);
        $slotEnd = Carbon::parse($date.' '.$endTime);
        $blockStart = Carbon::parse($date.' '.$this->time_from);
        $blockEnd = Carbon::parse($date.' '.$this->time_to);

        return $slotStart->lt($blockEnd) && $slotEnd->gt($blockStart);
    }

    public function coversDate(string $date): bool
    {
        return $this->date_from->lte(Carbon::parse($date))
            && $this->date_to->gte(Carbon::parse($date));
    }

    public function getDateRangeLabel(): string
    {
        if ($this->date_from->eq($this->date_to)) {
            return $this->date_from->format('d/m/Y');
        }

        return $this->date_from->format('d/m/Y').' – '.$this->date_to->format('d/m/Y');
    }
}
