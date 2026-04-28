<?php

return [
    // General
    'title' => 'Book Appointment',
    'page_title' => 'Book your appointment at :clinic',
    'powered_by' => 'Powered by',

    // Clinic info
    'about_clinic' => 'About us',
    'opening_hours' => 'Opening hours',
    'contact' => 'Contact',
    'address' => 'Address',
    'phone' => 'Phone',
    'email' => 'Email',
    'closed' => 'Closed',
    'days' => [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ],

    // Booking disabled / portal disabled
    'portal_disabled' => 'This clinic does not have the public portal enabled.',
    'booking_disabled' => 'Online booking is temporarily disabled. Please contact the clinic directly.',
    'booking_unavailable' => 'Bookings unavailable',
    'booking_unavailable_body' => 'This clinic is not accepting online bookings at the moment.',
    // Doctors section
    'our_doctors' => 'Our Doctors',
    'no_doctors' => 'No doctors are available at this time.',
    'select_doctor' => 'Select a doctor',
    'specialties' => 'Specialties',

    // Wizard steps
    'step' => 'Step :current of :total',
    'step_doctor' => 'Doctor',
    'step_datetime' => 'Date & time',
    'step_details' => 'Your details',
    'step_confirm' => 'Confirmation',

    // Date / time selection
    'select_date' => 'Select a date',
    'select_time' => 'Select a time slot',
    'no_slots_available' => 'No available slots on this date. Please select another day.',
    'date_not_available' => 'The clinic is closed on this day.',
    'date_too_soon' => 'Bookings must be made at least :hours hours in advance.',
    'date_too_far' => 'You can only book up to :days days in advance.',
    'morning' => 'Morning',
    'afternoon' => 'Afternoon',
    'evening' => 'Evening',

    // Patient details form
    'your_details' => 'Your details',
    'first_name' => 'First name',
    'last_name' => 'Last name',
    'phone_number' => 'Phone',
    'email_optional' => 'Email (optional)',
    'reason_for_visit' => 'Reason for visit',
    'reason_placeholder' => 'Briefly describe the reason for your visit...',
    'accept_terms' => 'I accept the processing of my data to manage the appointment',

    // Buttons
    'next' => 'Next',
    'back' => 'Back',
    'confirm_booking' => 'Confirm Booking',
    'book_again' => 'Book another appointment',
    'change_doctor' => 'Change doctor',
    'change_datetime' => 'Change date/time',

    // Confirmation
    'booking_confirmed' => 'Booking sent successfully!',
    'booking_pending_confirmation' => 'Your request was received. The clinic will review your booking and contact you to confirm.',
    'booking_auto_confirmed' => 'Your appointment is confirmed. We will see you on the selected date and time.',
    'appointment_details' => 'Appointment details',
    'appointment_with' => 'With',
    'appointment_at' => 'At',
    'reference_number' => 'Reference number',
    'save_reference' => 'Save this number for any inquiries',

    // Errors
    'error_generic' => 'An error occurred processing your booking. Please try again.',
    'error_slot_taken' => 'Sorry, that time slot was just taken. Please select another.',
    'error_too_many_requests' => 'Too many attempts. Please wait a moment and try again.',
    'error_clinic_full' => 'Sorry, the clinic cannot accept more bookings at this time.',

    // Validation
    'validation' => [
        'first_name_required' => 'First name is required.',
        'last_name_required' => 'Last name is required.',
        'phone_required' => 'Phone is required.',
        'email_invalid' => 'Email is invalid.',
        'doctor_required' => 'You must select a doctor.',
        'date_required' => 'You must select a date.',
        'time_required' => 'You must select a time slot.',
        'terms_required' => 'You must accept data processing.',
    ],
];
