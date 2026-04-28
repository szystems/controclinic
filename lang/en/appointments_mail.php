<?php

return [
    'greeting' => 'Hi :name,',

    // Subjects
    'booked_patient_subject' => 'Your appointment confirmation at :clinic',
    'booked_clinic_subject' => 'New online booking: :patient',
    'confirmed_subject' => 'Your appointment at :clinic has been confirmed',
    'cancelled_subject' => 'Your appointment at :clinic has been cancelled',
    'reminder_subject' => 'Reminder: your appointment tomorrow at :clinic',

    // Booked - patient
    'booked_patient_pending_intro' => 'We have received your appointment request at :clinic. The clinic will review availability and confirm shortly.',
    'booked_patient_confirmed_intro' => 'Your appointment at :clinic has been booked successfully!',
    'booked_patient_pending_note' => 'Status: pending confirmation. You will receive another email once it is confirmed.',
    'booked_patient_confirmed_note' => 'We look forward to seeing you on the date and time indicated. Please arrive 10 minutes early.',

    // Booked - clinic
    'booked_clinic_title' => 'New appointment booked online',
    'booked_clinic_intro' => 'A new booking was received from patient :patient through the public portal.',
    'booked_clinic_pending_note' => '⚠️ This appointment requires manual confirmation. Please review and confirm it from the dashboard.',
    'manage_button' => 'View appointment in dashboard',

    // Confirmed
    'confirmed_intro' => 'We confirm your appointment at :clinic has been approved and scheduled.',
    'confirmed_note' => 'We look forward to seeing you. If you need to reschedule, please contact us as soon as possible.',

    // Cancelled
    'cancelled_intro' => 'We regret to inform you that your appointment at :clinic has been cancelled.',
    'cancelled_note' => 'If you need to reschedule, please contact :clinic or book a new appointment online.',

    // Reminder
    'reminder_intro' => 'This is a reminder for your upcoming appointment at :clinic.',
    'reminder_note' => 'Please confirm your attendance and arrive 10 minutes early. If you cannot attend, let us know as soon as possible.',

    // Common labels
    'label_reference' => 'Reference',
    'label_patient' => 'Patient',
    'label_doctor' => 'Doctor',
    'label_date' => 'Date',
    'label_time' => 'Time',
    'label_phone' => 'Phone',
    'label_email' => 'Email',
    'label_status' => 'Status',
    'label_reason' => 'Reason',
    'label_cancellation_reason' => 'Cancellation reason',
    'clinic_info' => 'Clinic information',
    'cancellation_note' => 'If you cannot attend, please cancel in advance by contacting the clinic.',
];
