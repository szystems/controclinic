<?php

return [
    // Títulos
    'prescriptions' => 'Recetas',
    'new_prescription' => 'Nueva Receta',
    'edit_prescription' => 'Editar Receta',
    'prescription_detail' => 'Detalle de Receta',
    'draft' => 'Borrador',

    // Estados
    'status' => 'Estado',
    'all_statuses' => 'Todos los estados',
    'status_draft' => 'Borrador',
    'status_issued' => 'Emitida',
    'status_dispensed' => 'Dispensada',
    'status_cancelled' => 'Cancelada',

    // Campos
    'folio' => 'Folio',
    'patient' => 'Paciente',
    'doctor' => 'Médico',
    'diagnosis' => 'Diagnóstico',
    'diagnosis_placeholder' => 'Ej: Infección respiratoria aguda (CIE-10: J06.9)',
    'notes' => 'Instrucciones al paciente',
    'notes_placeholder' => 'Instrucciones generales, advertencias, seguimiento...',
    'internal_notes' => 'Notas internas',
    'internal_notes_hint' => 'Solo visible para el personal autorizado',
    'issued_at' => 'Fecha de emisión',
    'valid_until' => 'Válida hasta',
    'expired' => 'Vencida',
    'from_record' => 'Consulta relacionada',
    'view_record' => 'Ver consulta',

    // Medicamentos
    'medications' => 'Medicamentos',
    'medication' => 'Medicamento',
    'no_medications' => 'Sin medicamentos registrados.',
    'add_medication' => 'Agregar medicamento',
    'medication_name' => 'Nombre del medicamento',
    'medication_name_placeholder' => 'Ej: Ibuprofeno genérico / Advil 400mg',
    'active_ingredient' => 'Principio activo',
    'presentation' => 'Presentación',
    'presentation_placeholder' => 'Ej: comprimidos 400mg',
    'dose' => 'Dosis',
    'dose_placeholder' => 'Ej: 1 comprimido',
    'frequency' => 'Frecuencia',
    'frequency_placeholder' => 'Ej: cada 8 horas',
    'duration' => 'Duración',
    'duration_placeholder' => 'Ej: 7 días',
    'route' => 'Vía',
    'route_oral' => 'Oral',
    'route_topical' => 'Tópico',
    'route_injectable' => 'Inyectable',
    'route_inhalation' => 'Inhalación',
    'route_sublingual' => 'Sublingual',
    'route_ophthalmic' => 'Oftálmico',
    'route_otic' => 'Ótico',
    'route_rectal' => 'Rectal',
    'route_other' => 'Otro',
    'instructions' => 'Instrucciones',
    'instructions_placeholder' => 'Ej: Tomar con alimentos, evitar exposición solar',
    'quantity' => 'Cantidad',
    'is_controlled' => 'Sustancia controlada',
    'controlled' => 'Controlada',

    // Secciones
    'general_data' => 'Datos generales',
    'select_patient' => 'Seleccionar paciente',
    'search_patient' => 'Buscar por paciente',
    'search_patient_placeholder' => 'Buscar por nombre, teléfono, correo o identificación',
    'no_patient_found' => 'No se encontraron pacientes con ese criterio',

    // Acciones
    'save_draft' => 'Guardar borrador',
    'issue_prescription' => 'Emitir receta',
    'cancel_prescription' => 'Cancelar receta',
    'confirm_cancel_message' => '¿Estás seguro de cancelar esta receta? Esta acción no se puede deshacer.',
    'yes_cancel' => 'Sí, cancelar',

    // Mensajes
    'created_successfully' => 'Receta creada exitosamente.',
    'updated_successfully' => 'Receta actualizada.',
    'issued_successfully' => 'Receta emitida.',
    'cancelled_successfully' => 'Receta cancelada.',
    'no_prescriptions' => 'No hay recetas registradas.',
    'no_prescriptions_description' => 'Emite tu primera receta y entrégala al paciente con código QR de verificación.',
    'empty_state_bullet_1' => 'Ítems de medicamento con dosis, frecuencia y duración',
    'empty_state_bullet_2' => 'PDF imprimible con código QR de verificación pública',
    'empty_state_bullet_3' => 'Vinculada automáticamente al historial del paciente',

    // QR (Fase 2)
    'verify_prescription' => 'Verificar receta',
    'qr_valid' => 'Receta válida',
    'qr_invalid' => 'Receta no encontrada o inválida',
];
