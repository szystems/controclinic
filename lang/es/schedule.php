<?php

return [
    'title' => 'Mi horario',
    'subtitle' => 'Gestiona tus bloqueos de horario, vacaciones y días no disponibles',
    'new_block' => 'Nuevo bloqueo',
    'no_blocks' => 'No hay bloqueos de horario registrados',
    'no_blocks_desc' => 'Agrega un bloqueo para marcar días u horas en que no estarás disponible',

    // Form
    'form_title_create' => 'Agregar bloqueo de horario',
    'form_title_edit' => 'Editar bloqueo',
    'date_from' => 'Fecha desde',
    'date_to' => 'Fecha hasta',
    'all_day' => 'Todo el día',
    'partial_hours' => 'Bloqueo parcial (horas)',
    'time_from' => 'Hora desde',
    'time_to' => 'Hora hasta',
    'reason' => 'Motivo (opcional)',
    'reason_placeholder' => 'Ej: Vacaciones, Congreso médico, Consulta externa…',

    // Actions
    'save' => 'Guardar bloqueo',
    'cancel' => 'Cancelar',
    'delete' => 'Eliminar',
    'confirm_delete' => '¿Eliminar este bloqueo de horario?',

    // Messages
    'created' => 'Bloqueo de horario creado.',
    'updated' => 'Bloqueo de horario actualizado.',
    'deleted' => 'Bloqueo de horario eliminado.',

    // Conflict in appointment
    'doctor_unavailable' => 'El doctor no está disponible en la fecha/hora seleccionada.',

    // Labels in lists
    'all_day_label' => 'Todo el día',
    'partial_label' => ':from – :to',

    // Validation
    'date_to_after' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
    'time_to_after' => 'La hora de fin debe ser posterior a la hora de inicio.',

    // Doctor selector (owner/admin view)
    'doctor_label' => 'Doctor',
    'select_doctor' => 'Selecciona un doctor',
    'managing_for' => 'Gestionando horario de :name',
];
