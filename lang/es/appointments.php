<?php

return [
    // Títulos
    'title' => 'Citas',
    'appointment' => 'Cita',
    'new_appointment' => 'Nueva Cita',
    'edit_appointment' => 'Editar Cita',
    'appointment_details' => 'Detalles de la Cita',

    // Campos
    'patient' => 'Paciente',
    'doctor' => 'Doctor',
    'date' => 'Fecha',
    'time' => 'Hora',
    'start_time' => 'Hora de Inicio',
    'end_time' => 'Hora de Fin',
    'duration' => 'Duración',
    'reason' => 'Motivo de la Cita',
    'symptoms' => 'Síntomas',
    'notes' => 'Notas',
    'room' => 'Consultorio',

    // Tipos de cita
    'type' => 'Tipo de Cita',
    'scheduled' => 'Programada',
    'walk_in' => 'Orden de Llegada',
    'emergency' => 'Emergencia',
    'follow_up' => 'Seguimiento',
    'telemedicine' => 'Telemedicina',

    // Estados
    'status' => 'Estado',
    'status_scheduled' => 'Programada',
    'status_confirmed' => 'Confirmada',
    'status_waiting' => 'En Espera',
    'status_in_progress' => 'En Consulta',
    'status_completed' => 'Completada',
    'status_cancelled' => 'Cancelada',
    'status_no_show' => 'No se Presentó',

    // Sistema de fichas
    'queue_number' => 'Número de Ficha',
    'queue_period' => 'Turno',
    'morning' => 'Mañana',
    'afternoon' => 'Tarde',
    'evening' => 'Noche',

    // Acciones
    'check_in' => 'Registrar Llegada',
    'start_consultation' => 'Iniciar Consulta',
    'complete' => 'Completar',
    'cancel' => 'Cancelar',
    'reschedule' => 'Reprogramar',
    'confirm' => 'Confirmar',
    'mark_no_show' => 'Marcar como No Presentado',

    // Cancelación
    'cancellation_reason' => 'Motivo de Cancelación',
    'cancelled_by_patient' => 'Cancelado por el paciente',
    'cancelled_by_clinic' => 'Cancelado por la clínica',

    // Recordatorios
    'reminder' => 'Recordatorio',
    'reminder_sent' => 'Recordatorio Enviado',
    'send_reminder' => 'Enviar Recordatorio',

    // Calendario
    'calendar' => 'Calendario',
    'day_view' => 'Vista Día',
    'week_view' => 'Vista Semana',
    'month_view' => 'Vista Mes',
    'today' => 'Hoy',
    'available_slots' => 'Horarios Disponibles',
    'no_availability' => 'Sin Disponibilidad',

    // Tiempos
    'minutes' => 'minutos',
    'hours' => 'horas',

    // Mensajes
    'appointment_created' => 'Cita creada exitosamente',
    'appointment_updated' => 'Cita actualizada exitosamente',
    'appointment_cancelled' => 'Cita cancelada exitosamente',
    'appointment_confirmed' => 'Cita confirmada exitosamente',
    'no_appointments' => 'No hay citas programadas',
    'no_appointments_today' => 'No hay citas para hoy',
    'conflict_detected' => 'Se detectó un conflicto de horario',

    // Dashboard
    'todays_appointments' => 'Citas de Hoy',
    'upcoming_appointments' => 'Próximas Citas',
    'upcoming' => 'Próximas Citas',
    'no_upcoming' => 'Sin citas próximas',
    'total_appointments' => 'Total de Citas',
    'completed_today' => 'Completadas Hoy',
    'pending_today' => 'Pendientes Hoy',

    // Filters
    'all_statuses' => 'Todos los Estados',
    'all_doctors' => 'Todos los Doctores',
    'all_types' => 'Todos los Tipos',
    'filter_by_date' => 'Filtrar por Fecha',
    'filter_by_status' => 'Filtrar por Estado',
    'filter_by_doctor' => 'Filtrar por Doctor',

    // Workflow
    'checked_in_at' => 'Registró llegada',
    'started_at' => 'Inició consulta',
    'completed_at' => 'Completó cita',
    'cancelled_at' => 'Canceló cita',

    // Confirmations
    'confirm_cancel' => '¿Está seguro que desea cancelar esta cita?',
    'confirm_no_show' => '¿Está seguro que desea marcar a este paciente como no presentado?',

    // Select
    'select_patient' => 'Seleccionar Paciente',
    'select_doctor' => 'Seleccionar Doctor',
    'select_type' => 'Seleccionar Tipo',
    'search_patient' => 'Buscar paciente...',

    // Límites
    'limit_reached' => 'Has alcanzado el límite de citas mensuales de tu plan',
    'consultation' => 'Consulta',

    // Calendario (Fase 6)
    'calendar_hint' => 'Vista visual de citas. Arrastra para reagendar.',
    'list_view' => 'Lista',
    'status_legend' => 'Estado',

    // Comprobante / Lista PDF
    'appointment_voucher' => 'Comprobante de Cita',
    'voucher_note' => 'Por favor presente este comprobante el día de la cita. Si requiere reprogramar, contacte a la clínica con anticipación.',
    'agenda_title' => 'Agenda de Citas',
    'status_scheduled' => 'Programada',
    'status_confirmed' => 'Confirmada',
    'status_in_progress' => 'En curso',
    'status_completed' => 'Completada',
    'status_cancelled' => 'Cancelada',
    'rescheduled' => 'Cita reagendada correctamente',
    'no_patient' => '(Sin paciente)',
    'not_found' => 'Cita no encontrada',
];
