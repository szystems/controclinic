<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'color',
        'category',
        'description',
    ];

    // Colores disponibles (Tailwind)
    public const COLORS = [
        'gray', 'red', 'orange', 'amber', 'yellow',
        'lime', 'green', 'teal', 'cyan', 'blue',
        'indigo', 'violet', 'purple', 'pink', 'rose',
    ];

    // Categorías
    public const CATEGORY_PATIENT = 'patient';

    public const CATEGORY_GENERAL = 'general';

    // ==================== RELATIONSHIPS ====================

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patients(): MorphToMany
    {
        return $this->morphedByMany(Patient::class, 'taggable')
            ->withPivot('tagged_by', 'tagged_at')
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    public function scopeForClinic($query, string $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForPatients($query)
    {
        return $query->whereIn('category', [self::CATEGORY_PATIENT, self::CATEGORY_GENERAL]);
    }

    // ==================== HELPERS ====================

    /**
     * CSS classes para el badge del tag (bg + text en Tailwind).
     */
    public function getBadgeClassesAttribute(): string
    {
        $map = [
            'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
            'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
            'lime' => 'bg-lime-100 text-lime-700 dark:bg-lime-900/40 dark:text-lime-300',
            'green' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
            'teal' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
            'cyan' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300',
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
            'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
            'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
            'pink' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300',
            'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        ];

        return $map[$this->color] ?? $map['gray'];
    }

    /**
     * Dot color para selector de color.
     */
    public function getDotClassAttribute(): string
    {
        $map = [
            'gray' => 'bg-gray-400',
            'red' => 'bg-red-500',
            'orange' => 'bg-orange-500',
            'amber' => 'bg-amber-500',
            'yellow' => 'bg-yellow-500',
            'lime' => 'bg-lime-500',
            'green' => 'bg-green-500',
            'teal' => 'bg-teal-500',
            'cyan' => 'bg-cyan-500',
            'blue' => 'bg-blue-500',
            'indigo' => 'bg-indigo-500',
            'violet' => 'bg-violet-500',
            'purple' => 'bg-purple-500',
            'pink' => 'bg-pink-500',
            'rose' => 'bg-rose-500',
        ];

        return $map[$this->color] ?? 'bg-gray-400';
    }
}
