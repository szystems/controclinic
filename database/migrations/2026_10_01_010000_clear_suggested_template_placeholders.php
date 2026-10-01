<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The starter template used to copy editing instructions into SOAP fields.
     * Clear only values that still match those instructions exactly.
     */
    public function up(): void
    {
        $placeholders = [
            'chief_complaint' => [
                'Motivo de consulta (personaliza según tu especialidad)',
                'Reason for visit (customize for your specialty)',
            ],
            'present_illness' => [
                'Inicio, duración, síntomas asociados, factores que alivian o empeoran.',
                'Onset, duration, associated symptoms, relieving/aggravating factors.',
            ],
            'physical_examination' => [
                'Aspecto general, signos vitales, sistemas revisados según indicación.',
                'General appearance, vital signs, systems reviewed as indicated.',
            ],
            'assessment' => [
                'Impresión clínica / diagnóstico presuntivo.',
                'Clinical impression / working diagnosis.',
            ],
            'plan' => [
                'Tratamiento, seguimiento, indicaciones al paciente e interconsultas si aplica.',
                'Treatment, follow-up, patient instructions, and referrals if needed.',
            ],
        ];

        foreach (['record_templates', 'medical_records'] as $table) {
            foreach ($placeholders as $column => $values) {
                DB::table($table)->whereIn($column, $values)->update([$column => null]);
            }
        }
    }

    public function down(): void
    {
        // The previous values were template instructions, not clinical notes.
    }
};
