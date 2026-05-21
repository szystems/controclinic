<?php

return [
    // Page
    'title' => 'Centro de Ayuda',
    'subtitle' => 'Aprende a sacar el máximo partido a ControClinic.',
    'search_placeholder' => 'Buscar artículo...',
    'no_results' => 'No se encontraron artículos para ":query".',
    'read_more' => 'Leer más',
    'back_to_help' => '← Volver al Centro de Ayuda',
    'tips_title' => 'Consejos útiles',

    // Help banner
    'how_it_works' => '¿Cómo funciona :module?',
    'dismiss' => 'Cerrar',
    'view_help' => 'Ver ayuda completa',

    // Module descriptions (used in banner and /help index)
    'modules' => [
        'patients' => [
            'title' => 'Pacientes',
            'summary' => 'Registra y gestiona el expediente completo de cada paciente: datos de contacto, historial médico, citas y documentos adjuntos.',
            'tips' => [
                'Usa la búsqueda para encontrar pacientes por nombre, teléfono o correo.',
                'Accede al historial médico desde la ficha del paciente.',
                'Puedes archivar pacientes inactivos sin eliminar sus datos.',
            ],
        ],
        'appointments' => [
            'title' => 'Citas',
            'summary' => 'Agenda, confirma y gestiona todas las citas de tu clínica. Filtra por doctor, estado o rango de fechas.',
            'tips' => [
                'Los estados de cita son: Pendiente, Confirmada, En progreso, Completada y Cancelada.',
                'Usa "Nueva cita" para reservar directamente sin pasar por el portal.',
                'Las citas confirmadas envían recordatorio automático si el correo está configurado.',
            ],
        ],
        'medical-records' => [
            'title' => 'Historiales médicos',
            'summary' => 'Documenta cada consulta con notas de evolución, diagnósticos, recetas y archivos adjuntos.',
            'tips' => [
                'Cada historial queda vinculado al paciente y a la cita correspondiente.',
                'Puedes adjuntar imágenes, PDFs y resultados de laboratorio.',
                'Los historiales son privados y solo accesibles por doctores y el owner.',
            ],
        ],
        'invoices' => [
            'title' => 'Facturación',
            'summary' => 'Crea facturas, registra pagos y lleva el control de ingresos de tu clínica.',
            'tips' => [
                'Puedes crear una factura directamente desde una cita completada.',
                'Los estados son: Borrador, Enviada, Pagada, Vencida y Cancelada.',
                'Exporta el listado en CSV para tu contador.',
            ],
        ],
        'prescriptions' => [
            'title' => 'Recetas',
            'summary' => 'Genera recetas médicas digitales vinculadas al paciente y al historial de la consulta.',
            'tips' => [
                'Las recetas incluyen medicamento, dosis, frecuencia y duración.',
                'Puedes imprimir o enviar la receta por correo al paciente.',
            ],
        ],
        'staff' => [
            'title' => 'Equipo',
            'summary' => 'Gestiona los usuarios de tu clínica: invita miembros, asigna roles y controla permisos.',
            'tips' => [
                'Los roles disponibles son: Doctor, Asistente, Recepcionista y Secretaria.',
                'Invita a un nuevo miembro con su correo electrónico.',
                'Puedes desactivar temporalmente a un usuario sin eliminarlo.',
            ],
        ],
        'reports' => [
            'title' => 'Reportes',
            'summary' => 'Analiza el rendimiento de tu clínica con reportes de ingresos, citas y ocupación.',
            'tips' => [
                'Filtra los reportes por rango de fechas o por doctor.',
                'Los gráficos de ingresos comparan mes a mes automáticamente.',
            ],
        ],
        'schedule' => [
            'title' => 'Horarios',
            'summary' => 'Configura los días y horas de atención de tu clínica para el portal de reservas en línea.',
            'tips' => [
                'Puedes definir horarios distintos para cada día de la semana.',
                'Los horarios afectan directamente la disponibilidad en el portal público.',
            ],
        ],
    ],

    // Tooltips — appointment statuses
    'tooltip' => [
        'appointment_pending' => 'La cita fue solicitada pero aún no ha sido confirmada por la clínica.',
        'appointment_confirmed' => 'La cita está confirmada. El paciente recibirá recordatorio.',
        'appointment_in_progress' => 'El paciente está siendo atendido en este momento.',
        'appointment_completed' => 'La consulta finalizó correctamente.',
        'appointment_cancelled' => 'La cita fue cancelada. Puede reagendarse.',
        'appointment_no_show' => 'El paciente no se presentó a la cita.',

        'record_type_consultation' => 'Nota de primera consulta o revisión general.',
        'record_type_follow_up' => 'Seguimiento de un tratamiento o diagnóstico previo.',
        'record_type_procedure' => 'Procedimiento clínico o intervención menor.',
        'record_type_prescription' => 'Emisión de receta médica.',

        'payment_cash' => 'Pago en efectivo recibido en clínica.',
        'payment_card' => 'Pago con tarjeta de crédito o débito.',
        'payment_transfer' => 'Transferencia bancaria.',
        'payment_insurance' => 'Cubierto por aseguradora médica.',

        'role_owner' => 'Acceso total: configuración, reportes, facturación y equipo.',
        'role_doctor' => 'Acceso a pacientes, citas, historiales y recetas.',
        'role_assistant' => 'Apoyo clínico: pacientes, citas e historiales.',
        'role_secretary' => 'Gestión administrativa: citas y pacientes.',
        'role_receptionist' => 'Recepción: agenda citas y registra pacientes.',
    ],
];
