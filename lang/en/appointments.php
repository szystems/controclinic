<?php

return [
    // Titles
    'title' => 'Appointments',
    'appointment' => 'Appointment',
    'new_appointment' => 'New Appointment',
    'edit_appointment' => 'Edit Appointment',
    'appointment_details' => 'Appointment Details',

    // Fields
    'patient' => 'Patient',
    'doctor' => 'Doctor',
    'date' => 'Date',
    'time' => 'Time',
    'start_time' => 'Start Time',
    'end_time' => 'End Time',
    'duration' => 'Duration',
    'reason' => 'Reason for Visit',
    'symptoms' => 'Symptoms',
    'notes' => 'Notes',
    'room' => 'Room',

    // Appointment types
    'type' => 'Appointment Type',
    'scheduled' => 'Scheduled',
    'walk_in' => 'Walk-in',
    'emergency' => 'Emergency',
    'follow_up' => 'Follow-up',
    'telemedicine' => 'Telemedicine',

    // Status
    'status' => 'Status',
    'status_scheduled' => 'Scheduled',
    'scheduled_by' => 'Scheduled by',
    'status_confirmed' => 'Confirmed',
    'status_waiting' => 'Waiting',
    'status_in_progress' => 'In Progress',
    'status_completed' => 'Completed',
    'status_cancelled' => 'Cancelled',
    'status_no_show' => 'No Show',

    // Queue system
    'queue_number' => 'Queue Number',
    'queue_period' => 'Period',
    'morning' => 'Morning',
    'afternoon' => 'Afternoon',
    'evening' => 'Evening',

    // Actions
    'check_in' => 'Check In',
    'start_consultation' => 'Start Consultation',
    'complete' => 'Complete',
    'cancel' => 'Cancel',
    'reschedule' => 'Reschedule',
    'confirm' => 'Confirm',
    'mark_no_show' => 'Mark as No Show',

    // Cancellation
    'cancellation_reason' => 'Cancellation Reason',
    'cancelled_by_patient' => 'Cancelled by patient',
    'cancelled_by_clinic' => 'Cancelled by clinic',

    // Reminders
    'reminder' => 'Reminder',
    'reminder_sent' => 'Reminder Sent',
    'send_reminder' => 'Send Reminder',

    // Calendar
    'calendar' => 'Calendar',
    'day_view' => 'Day View',
    'week_view' => 'Week View',
    'month_view' => 'Month View',
    'today' => 'Today',
    'available_slots' => 'Available Slots',
    'no_availability' => 'No Availability',

    // Time
    'minutes' => 'minutes',
    'hours' => 'hours',

    // Messages
    'appointment_created' => 'Appointment created successfully',
    'appointment_updated' => 'Appointment updated successfully',
    'appointment_cancelled' => 'Appointment cancelled successfully',
    'appointment_confirmed' => 'Appointment confirmed successfully',
    'no_appointments' => 'No appointments to show',
    'no_appointments_today' => 'No appointments for today',
    'no_appointments_description' => 'Schedule your first appointment to start organizing consultations.',
    'empty_state_bullet_1' => 'Automatic email confirmation with cancellation link',
    'empty_state_bullet_2' => 'Daily and weekly calendar view per doctor',
    'empty_state_bullet_3' => 'Automatic schedule conflict detection',
    'conflict_detected' => 'Schedule conflict detected',

    // Dashboard
    'todays_appointments' => "Today's Appointments",
    'upcoming_appointments' => 'Upcoming Appointments',
    'upcoming' => 'Upcoming Appointments',
    'no_upcoming' => 'No upcoming appointments',
    'total_appointments' => 'Total Appointments',
    'completed_today' => 'Completed Today',
    'pending_today' => 'Pending Today',

    // Filters
    'all_statuses' => 'All Statuses',
    'all_doctors' => 'All Doctors',
    'all_types' => 'All Types',
    'filter_by_date' => 'Filter by Date',
    'filter_by_status' => 'Filter by Status',
    'filter_by_doctor' => 'Filter by Doctor',

    // Workflow
    'checked_in_at' => 'Checked in at',
    'started_at' => 'Started at',
    'completed_at' => 'Completed at',
    'cancelled_at' => 'Cancelled at',

    // Confirmations
    'confirm_cancel' => 'Are you sure you want to cancel this appointment?',
    'confirm_no_show' => 'Are you sure you want to mark this patient as no-show?',

    // Select
    'select_patient' => 'Select Patient',
    'select_doctor' => 'Select Doctor',
    'select_type' => 'Select Type',
    'search_patient' => 'Search patient...',

    // Limits
    'limit_reached' => 'You have reached your plan\'s monthly appointment limit',
    'consultation' => 'Consultation',

    // Calendar (Phase 6)
    'calendar_hint' => 'Visual appointment view. Drag to reschedule.',
    'list_view' => 'List',
    'status_legend' => 'Status',

    // Voucher / PDF list
    'appointment_voucher' => 'Appointment Voucher',
    'voucher_note' => 'Please bring this voucher to your appointment. If you need to reschedule, contact the clinic in advance.',
    'agenda_title' => 'Appointments Agenda',
    'status_scheduled' => 'Scheduled',
    'status_confirmed' => 'Confirmed',
    'status_in_progress' => 'In progress',
    'status_completed' => 'Completed',
    'status_cancelled' => 'Cancelled',
    'rescheduled' => 'Appointment rescheduled successfully',
    'no_patient' => '(No patient)',
    'not_found' => 'Appointment not found',
    'internal_comments' => 'Internal Comments',
    'no_comments' => 'No comments yet.',
    'add_comment' => 'Add',
    'add_comment_placeholder' => 'Write an internal comment...',
    'comment_added' => 'Comment added',
    'comment_deleted' => 'Comment deleted',
    'confirm_delete_comment' => 'Delete this comment?',

    // Manual reminders
    'send_email_reminder' => 'Send email reminder',
    'send_whatsapp_reminder' => 'WhatsApp reminder',
    'confirm_send_reminder' => 'Send an appointment reminder to the patient by email?',
    'reminder_sent' => 'Reminder sent successfully',
    'reminder_no_email' => 'The patient has no email on file',
    'confirmed_via_link' => 'Confirmed by patient',

    // Flow explanations for the interface
    'create_staff_hint' => 'Appointments created here are <strong>automatically confirmed</strong>. The patient will receive an email with a cancellation link in case they need to cancel.',
    'created_via_staff' => 'Created by staff',
    'created_via_public' => 'Online booking by patient',
    'status_hint_scheduled_public' => 'This appointment was booked by the patient via the portal. They received an email with a link to confirm or cancel.',
    'status_hint_confirmed_staff' => 'Appointment confirmed. It was scheduled directly by the clinic staff.',
    'status_hint_confirmed_link' => 'The patient confirmed attendance via the link sent by email.',

    'whatsapp_reminder_message' => 'Hi :patient, this is a reminder for your appointment with :doctor on :date at :time at :clinic. See you soon!',

    // Billing
    'billing_section' => 'Consultation price',
    'consultation_price' => 'Price',
    'consultation_discount' => 'Discount',
    'is_billable' => 'Billable',
    'is_billable_hint' => 'Mark this appointment as billable to the patient',
    'price_optional' => 'Leave blank to use the clinic default price',

    // Multi-doctor schedule view
    'schedule_view_title' => 'Day schedule',
    'schedule_view_hint' => 'See all doctors\' appointments side by side, by hour.',
    'calendar_view' => 'Calendar',
    'appointment_count' => '{1} 1 appointment|[2,*] :count appointments',
    'no_doctors_registered' => 'No doctors registered in this clinic.',
    'time' => 'Time',
    // Sprint C — new filters and columns
    'date_time' => 'Date / Time',
    'date_from' => 'From',
    'date_to' => 'To',
    'created_via' => 'Origin',
    'all_origins' => 'All origins',
    'origin_web' => 'Web',
    'origin_app' => 'App',
    'origin_phone' => 'Phone',
    'origin_walkin' => 'Walk-in',
    'price' => 'Price',
    'invoiced' => 'Invoiced',
];
