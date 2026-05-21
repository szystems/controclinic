<?php

return [
    // General
    'title' => 'Reservar Cita',
    'page_title' => 'Reserva tu cita en :clinic',
    'powered_by' => 'Desarrollado con',

    // Clinic info
    'about_clinic' => 'Sobre nosotros',
    'opening_hours' => 'Horario de atención',
    'contact' => 'Contacto',
    'address' => 'Dirección',
    'phone' => 'Teléfono',
    'email' => 'Correo electrónico',
    'closed' => 'Cerrado',
    'days' => [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ],

    // Booking disabled / portal disabled
    'portal_disabled' => 'Esta clínica no tiene habilitado el portal público.',
    'booking_disabled' => 'Las reservas en línea están temporalmente deshabilitadas. Por favor contacta directamente a la clínica.',
    'booking_unavailable' => 'Reservas no disponibles',
    'booking_unavailable_body' => 'Esta clínica no está aceptando reservas en línea por el momento.',

    // Doctors section
    'our_doctors' => 'Nuestros Doctores',
    'no_doctors' => 'No hay doctores disponibles en este momento.',
    'select_doctor' => 'Selecciona un doctor',
    'specialties' => 'Especialidades',

    // Wizard steps
    'step' => 'Paso :current de :total',
    'step_doctor' => 'Doctor',
    'step_datetime' => 'Fecha y hora',
    'step_details' => 'Tus datos',
    'step_confirm' => 'Confirmación',

    // Date / time selection
    'select_date' => 'Selecciona una fecha',
    'select_time' => 'Selecciona un horario',
    'no_slots_available' => 'No hay horarios disponibles para esta fecha. Por favor selecciona otro día.',
    'date_not_available' => 'La clínica no atiende este día.',
    'date_too_soon' => 'Las reservas deben hacerse con al menos :hours horas de anticipación.',
    'date_too_far' => 'Solo se pueden reservar citas hasta :days días en el futuro.',
    'morning' => 'Mañana',
    'afternoon' => 'Tarde',
    'evening' => 'Noche',

    // Patient details form
    'your_details' => 'Tus datos',
    'first_name' => 'Nombre',
    'last_name' => 'Apellido',
    'phone_number' => 'Teléfono',
    'email_optional' => 'Correo electrónico (opcional)',
    'reason_for_visit' => 'Motivo de la consulta',
    'reason_placeholder' => 'Describe brevemente el motivo de tu visita...',
    'accept_terms' => 'Acepto el procesamiento de mis datos para gestionar la cita',

    // Buttons
    'next' => 'Siguiente',
    'back' => 'Atrás',
    'confirm_booking' => 'Confirmar Reserva',
    'book_again' => 'Reservar otra cita',
    'change_doctor' => 'Cambiar doctor',
    'change_datetime' => 'Cambiar fecha/hora',

    // Confirmation
    'booking_confirmed' => '¡Reserva enviada con éxito!',
    'booking_pending_confirmation' => 'Tu solicitud fue recibida. La clínica revisará tu reserva y te contactará para confirmar.',
    'booking_auto_confirmed' => 'Tu cita está confirmada. Te esperamos en la fecha y hora seleccionadas.',
    'appointment_details' => 'Detalles de la cita',
    'appointment_with' => 'Con',
    'appointment_at' => 'En',
    'reference_number' => 'Número de referencia',
    'save_reference' => 'Guarda este número para cualquier consulta',

    // Errors
    'error_generic' => 'Ocurrió un error procesando tu reserva. Inténtalo de nuevo.',
    'error_slot_taken' => 'Lo sentimos, ese horario acaba de ser reservado. Por favor selecciona otro.',
    'error_too_many_requests' => 'Demasiados intentos. Espera un momento y vuelve a intentarlo.',
    'error_clinic_full' => 'Lo sentimos, la clínica no puede aceptar más reservas en este momento.',

    // Validation
    'validation' => [
        'first_name_required' => 'El nombre es obligatorio.',
        'last_name_required' => 'El apellido es obligatorio.',
        'phone_required' => 'El teléfono es obligatorio.',
        'email_invalid' => 'El correo electrónico no es válido.',
        'doctor_required' => 'Debes seleccionar un doctor.',
        'date_required' => 'Debes seleccionar una fecha.',
        'time_required' => 'Debes seleccionar un horario.',
        'terms_required' => 'Debes aceptar el procesamiento de datos.',
    ],

    'book_appointment' => 'Reservar cita',
    'about_us' => 'Sobre nosotros',
    'our_services' => 'Nuestros servicios',
    'our_team' => 'Nuestro equipo',
];
