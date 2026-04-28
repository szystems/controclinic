<?php

return [
    'greeting' => 'Hola :name,',

    // Subjects
    'booked_patient_subject' => 'Confirmación de tu cita en :clinic',
    'booked_clinic_subject' => 'Nueva reserva online: :patient',
    'confirmed_subject' => 'Tu cita en :clinic ha sido confirmada',
    'cancelled_subject' => 'Tu cita en :clinic ha sido cancelada',
    'reminder_subject' => 'Recordatorio: tu cita mañana en :clinic',

    // Booked - patient
    'booked_patient_pending_intro' => 'Hemos recibido tu solicitud de cita en :clinic. La clínica revisará la disponibilidad y te confirmará en breve.',
    'booked_patient_confirmed_intro' => '¡Tu cita en :clinic ha sido reservada con éxito!',
    'booked_patient_pending_note' => 'Estado: pendiente de confirmación. Recibirás otro correo cuando se confirme.',
    'booked_patient_confirmed_note' => 'Te esperamos en la fecha y hora indicadas. Por favor, llega 10 minutos antes.',

    // Booked - clinic
    'booked_clinic_title' => 'Nueva cita reservada online',
    'booked_clinic_intro' => 'Se ha recibido una nueva reserva del paciente :patient a través del portal público.',
    'booked_clinic_pending_note' => '⚠️ Esta cita requiere confirmación manual. Revísala y confírmala desde el panel.',
    'manage_button' => 'Ver cita en el panel',

    // Confirmed
    'confirmed_intro' => 'Te confirmamos que tu cita en :clinic ha sido aprobada y agendada.',
    'confirmed_note' => 'Te esperamos en la fecha y hora indicadas. Si necesitas reprogramar, contáctanos lo antes posible.',

    // Cancelled
    'cancelled_intro' => 'Lamentamos informarte que tu cita en :clinic ha sido cancelada.',
    'cancelled_note' => 'Si necesitas reprogramar, ponte en contacto con :clinic o agenda una nueva cita en línea.',

    // Reminder
    'reminder_intro' => 'Te recordamos tu próxima cita en :clinic.',
    'reminder_note' => 'Por favor, confirma tu asistencia y llega 10 minutos antes. Si no puedes asistir, avísanos cuanto antes.',

    // Common labels
    'label_reference' => 'Referencia',
    'label_patient' => 'Paciente',
    'label_doctor' => 'Doctor/a',
    'label_date' => 'Fecha',
    'label_time' => 'Hora',
    'label_phone' => 'Teléfono',
    'label_email' => 'Email',
    'label_status' => 'Estado',
    'label_reason' => 'Motivo',
    'label_cancellation_reason' => 'Motivo de cancelación',
    'clinic_info' => 'Información de la clínica',
    'cancellation_note' => 'Si no puedes asistir, te pedimos que canceles con antelación contactando a la clínica.',
];
