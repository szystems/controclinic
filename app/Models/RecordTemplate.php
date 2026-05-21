<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RecordTemplate extends Model
{
    use BelongsToClinic, HasUuids, LogsActivity, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'clinic_id',
        'created_by_user_id',
        'name',
        'specialty',
        'record_type',
        'chief_complaint',
        'present_illness',
        'physical_examination',
        'assessment',
        'plan',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ==================== ACTIVITY LOG ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'record_type', 'specialty', 'is_default'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ==================== RELATIONSHIPS ====================

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ==================== SCOPES ====================

    public function scopeForRecordType($query, string $type)
    {
        return $query->where('record_type', $type);
    }

    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }

    // ==================== METHODS ====================

    /**
     * Return an array with the SOAP fields ready to fill the Create form.
     */
    public function toSoapArray(): array
    {
        return [
            'chiefComplaint' => $this->chief_complaint ?? '',
            'presentIllness' => $this->present_illness ?? '',
            'physicalExamination' => $this->physical_examination ?? '',
            'assessment' => $this->assessment ?? '',
            'plan' => $this->plan ?? '',
        ];
    }
}
