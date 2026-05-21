<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'order',
        'medication_name',
        'active_ingredient',
        'presentation',
        'dose',
        'frequency',
        'duration',
        'route',
        'instructions',
        'quantity',
        'is_controlled',
    ];

    protected $casts = [
        'order' => 'integer',
        'quantity' => 'integer',
        'is_controlled' => 'boolean',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
