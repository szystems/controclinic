<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PatientFile extends Model
{
    use BelongsToClinic, HasUuids, LogsActivity, SoftDeletes;

    public const CATEGORIES = ['lab', 'image', 'report', 'prescription', 'consent', 'other'];

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'medical_record_id',
        'uploaded_by_user_id',
        'category',
        'name',
        'original_filename',
        'disk_path',
        'disk',
        'mime_type',
        'size_bytes',
        'notes',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    // ==================== Relations ====================

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    // ==================== Helpers ====================

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function formattedSize(): string
    {
        if ($this->size_bytes < 1024) {
            return $this->size_bytes.' B';
        }
        if ($this->size_bytes < 1048576) {
            return round($this->size_bytes / 1024, 1).' KB';
        }

        return round($this->size_bytes / 1048576, 1).' MB';
    }

    // ==================== Activity Log ====================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
