<?php

return [
    // Navigation
    'medical_records' => 'Historial Médico',
    'records' => 'Consultas',
    'new_record' => 'Nueva consulta',
    'edit_record' => 'Editar consulta',
    'view_record' => 'Ver consulta',
    'back_to_patient' => '← Volver al paciente',
    'back_to_records' => '← Volver al historial',

    // Index
    'subtitle' => 'Historial clínico de :patient',
    'no_records' => 'Aún no hay consultas registradas para este paciente.',
    'create_first' => 'Crear primera consulta',
    'filter_type' => 'Tipo',
    'filter_all_types' => 'Todos los tipos',
    'filter_status' => 'Estado',
    'filter_all_statuses' => 'Todos',
    'records_count' => ':count consulta|:count consultas',

    // Form
    'form_section_general' => 'Información general',
    'form_section_clinical' => 'Datos clínicos (SOAP)',
    'form_section_vital_signs' => 'Signos vitales',
    'form_section_diagnoses' => 'Diagnósticos',
    'form_section_prescriptions' => 'Prescripciones',
    'form_section_confidentiality' => 'Confidencialidad',

    'field_record_type' => 'Tipo de registro',
    'field_title' => 'Título',
    'field_title_placeholder' => 'Ej: Consulta general por dolor abdominal',
    'field_appointment' => 'Cita asociada',
    'field_appointment_none' => 'Sin cita asociada',
    'field_chief_complaint' => 'Motivo de consulta (S)',
    'field_chief_complaint_help' => 'Lo que refiere el paciente.',
    'field_present_illness' => 'Enfermedad actual (S)',
    'field_physical_examination' => 'Examen físico (O)',
    'field_assessment' => 'Evaluación / Diagnóstico clínico (A)',
    'field_plan' => 'Plan de tratamiento (P)',

    'field_temperature' => 'Temperatura (°C)',
    'field_heart_rate' => 'Frecuencia cardíaca (lpm)',
    'field_blood_pressure' => 'Presión arterial',
    'field_respiratory_rate' => 'Frecuencia respiratoria (rpm)',
    'field_oxygen_saturation' => 'Saturación O₂ (%)',
    'field_weight' => 'Peso (kg)',
    'field_height' => 'Estatura (cm)',

    'field_diagnoses' => 'Diagnósticos',
    'field_diagnosis_code' => 'Código (CIE-10)',
    'field_diagnosis_description' => 'Descripción',
    'add_diagnosis' => '+ Agregar diagnóstico',
    'remove_diagnosis' => 'Eliminar',

    'field_prescriptions' => 'Prescripciones',
    'field_prescription_drug' => 'Medicamento',
    'field_prescription_dosage' => 'Dosis',
    'field_prescription_duration' => 'Duración',
    'field_prescription_notes' => 'Notas',
    'add_prescription' => '+ Agregar prescripción',
    'remove_prescription' => 'Eliminar',

    'field_is_confidential' => 'Marcar como confidencial',
    'field_is_confidential_help' => 'Sólo será visible para roles autorizados.',
    'field_status' => 'Estado',

    // Buttons
    'save_draft' => 'Guardar borrador',
    'save_final' => 'Finalizar consulta',
    'save_changes' => 'Guardar cambios',
    'cancel' => 'Cancelar',

    // Status / Types
    'status_draft' => 'Borrador',
    'status_final' => 'Finalizado',
    'status_amended' => 'Modificado',
    'type_consultation' => 'Consulta',
    'type_diagnosis' => 'Diagnóstico',
    'type_prescription' => 'Receta',
    'type_lab_result' => 'Laboratorio',
    'type_imaging' => 'Imagenología',
    'type_procedure' => 'Procedimiento',
    'type_surgery' => 'Cirugía',
    'type_referral' => 'Referencia',
    'type_follow_up_note' => 'Seguimiento',
    'type_vital_signs' => 'Signos vitales',
    'type_vaccination' => 'Vacunación',
    'type_other' => 'Otro',

    // Show
    'created_by' => 'Atendido por',
    'created_at' => 'Fecha',
    'finalized_at' => 'Finalizado',
    'updated_at' => 'Última edición',
    'no_data' => 'Sin información',

    // Flash
    'created' => 'Consulta creada exitosamente.',
    'updated' => 'Consulta actualizada.',
    'deleted' => 'Consulta eliminada.',
    'finalized' => 'Consulta finalizada.',

    // Errors
    'cannot_edit_finalized' => 'Esta consulta está finalizada y no puede editarse. Crea una nueva consulta para registrar cambios.',
    'finalized_notice' => 'Esta consulta está finalizada y forma parte del historial clínico inmutable. Para registrar correcciones o nuevos hallazgos, crea una nueva consulta.',
    'permission_denied' => 'No tienes permiso para esta acción.',
    'confidential_hidden' => 'Esta consulta es confidencial.',

    // Confirm
    'confirm_delete' => '¿Eliminar esta consulta? Esta acción no se puede deshacer.',
    'confirm_finalize' => '¿Finalizar la consulta? Una vez finalizada no podrá editarse.',

    // Prescription PDF
    'prescription_pdf_title' => 'Receta Médica',
    'prescription_pdf_subtitle' => 'Documento médico oficial',
    'prescription_indication' => 'Indicaciones',
    'prescription_issued_at' => 'Expedida',
    'prescription_footer_note' => 'Esta receta es válida únicamente con la firma del médico tratante. Consúvelo ante cualquier duda.',
    'no_prescriptions' => 'Esta consulta no tiene prescripciones registradas.',
    'export_prescription' => 'Imprimir Receta',
    'field_diagnoses' => 'Diagnósticos',
    'field_prescriptions' => 'Prescripciones',
];
