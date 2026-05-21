<?php

return [
    'title' => 'Reportes',
    'subtitle' => 'Análisis y estadísticas de tu clínica',

    // Period selector
    'period' => 'Período',
    'period_today' => 'Hoy',
    'period_this_week' => 'Esta semana',
    'period_this_month' => 'Este mes',
    'period_last_month' => 'Mes anterior',
    'period_this_quarter' => 'Este trimestre',
    'period_this_year' => 'Este año',
    'period_custom' => 'Personalizado',
    'date_from' => 'Desde',
    'date_to' => 'Hasta',

    // Filters
    'filters' => 'Filtros',
    'all_doctors' => 'Todos los doctores',
    'all_statuses' => 'Todos los estados',
    'all_types' => 'Todos los tipos',

    // Summary cards
    'total_appointments' => 'Total Citas',
    'completed' => 'Completadas',
    'cancelled' => 'Canceladas',
    'no_show' => 'No asistió',
    'completion_rate' => 'Tasa de completado',
    'avg_duration' => 'Duración media',
    'avg_duration_help' => 'Promedio de minutos en citas completadas.',
    'minutes' => 'min',
    'previous_period_compare' => 'Comparativa con periodo anterior',
    'previous_period_help' => 'Cada KPI muestra el cambio porcentual frente al mismo número de días inmediatamente anterior. Una flecha verde indica mejora, roja indica deterioro. Las cancelaciones y ausencias se invierten (bajar es mejor).',
    'top_doctors' => 'Top doctores',
    'new_patients' => 'Pacientes nuevos',

    // Charts
    'chart_appointments_by_day' => 'Citas por día',
    'chart_appointments_by_status' => 'Citas por estado',
    'chart_appointments_by_type' => 'Citas por tipo',
    'chart_new_patients_by_month' => 'Pacientes nuevos por mes (últimos 6 meses)',

    // Status labels
    'status_scheduled' => 'Programada',
    'status_confirmed' => 'Confirmada',
    'status_waiting' => 'En espera',
    'status_in_progress' => 'En progreso',
    'status_completed' => 'Completada',
    'status_cancelled' => 'Cancelada',
    'status_no_show' => 'No asistió',

    // Type labels
    'type_scheduled' => 'Programada',
    'type_walk_in' => 'Sin cita',
    'type_emergency' => 'Emergencia',
    'type_follow_up' => 'Seguimiento',
    'type_telemedicine' => 'Telemedicina',

    // Export
    'export_csv' => 'Exportar CSV',
    'print_pdf' => 'Imprimir PDF',
    'generated_at' => 'Generado el',
    'clear_filters' => 'Limpiar filtros',
    'col_date' => 'Fecha',
    'col_time' => 'Hora',
    'col_patient' => 'Paciente',
    'col_doctor' => 'Doctor',
    'col_type' => 'Tipo',
    'col_status' => 'Estado',
    'col_duration' => 'Duración (min)',
    'col_reason' => 'Motivo',

    // No data
    'no_data' => 'Sin datos para el período seleccionado',

    // Billing / Revenue section
    'billing_section' => 'Ingresos',
    'billing_section_subtitle' => 'Resumen financiero del período (solo facturas emitidas en el período)',
    'billing_disabled_hint' => 'Habilita la facturación en Configuración para ver reportes de ingresos.',
    'total_invoiced' => 'Total Facturado',
    'total_invoiced_help' => 'Suma de facturas emitidas en el período (excluye borradores y canceladas).',
    'total_collected' => 'Total Cobrado',
    'total_collected_help' => 'Suma de pagos recibidos en el período.',
    'pending_revenue' => 'Pendiente de Cobro',
    'pending_revenue_help' => 'Saldo pendiente en facturas activas (no filtrado por período).',
    'average_ticket' => 'Ticket Promedio',
    'average_ticket_help' => 'Promedio del total de facturas emitidas en el período.',
    'revenue_by_doctor' => 'Ingresos por Doctor',
    'revenue_by_payment_method' => 'Ingresos por Método de Pago',
    'chart_revenue_by_day' => 'Cobros por día',
    'col_invoiced' => 'Facturado',
    'col_collected' => 'Cobrado',
    'col_invoices' => 'Facturas',
    'col_payments' => 'Pagos',
    'col_method' => 'Método',
    'col_amount' => 'Monto',
];
