<?php

namespace App\Traits;

use App\Models\Clinic;

/**
 * Trait para modelos que pertenecen a una clínica (tenant)
 * Auto-filtra y auto-asigna clinic_id
 */
trait BelongsToClinic
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToClinic(): void
    {
        // Auto-filtrar por clinic_id en todas las queries
        static::addGlobalScope('clinic', function ($query) {
            if ($clinic = app('current_clinic')) {
                $query->where($query->getModel()->getTable() . '.clinic_id', $clinic->id);
            }
        });

        // Auto-asignar clinic_id al crear
        static::creating(function ($model) {
            if (empty($model->clinic_id) && $clinic = app('current_clinic')) {
                $model->clinic_id = $clinic->id;
            }
        });
    }

    /**
     * Scope para obtener registros de una clínica específica
     */
    public function scopeForClinic($query, string|Clinic $clinic)
    {
        $clinicId = $clinic instanceof Clinic ? $clinic->id : $clinic;
        return $query->withoutGlobalScope('clinic')->where('clinic_id', $clinicId);
    }

    /**
     * Scope para obtener registros sin filtro de clínica
     */
    public function scopeWithoutClinicScope($query)
    {
        return $query->withoutGlobalScope('clinic');
    }
}
