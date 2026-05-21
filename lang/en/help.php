<?php

return [
    // Page
    'title' => 'Help Centre',
    'subtitle' => 'Learn how to get the most out of ControClinic.',
    'search_placeholder' => 'Search article...',
    'no_results' => 'No articles found for ":query".',
    'read_more' => 'Read more',
    'back_to_help' => '← Back to Help Centre',    'tips_title' => 'Useful tips',
    // Help banner
    'how_it_works' => 'How does :module work?',
    'dismiss' => 'Dismiss',
    'view_help' => 'View full help',

    // Module descriptions
    'modules' => [
        'patients' => [
            'title' => 'Patients',
            'summary' => 'Register and manage each patient\'s full record: contact details, medical history, appointments, and attachments.',
            'tips' => [
                'Use the search bar to find patients by name, phone, or email.',
                'Access the medical history from the patient\'s profile.',
                'You can archive inactive patients without deleting their data.',
            ],
        ],
        'appointments' => [
            'title' => 'Appointments',
            'summary' => 'Schedule, confirm, and manage all your clinic\'s appointments. Filter by doctor, status, or date range.',
            'tips' => [
                'Appointment statuses: Pending, Confirmed, In Progress, Completed, and Cancelled.',
                'Use "New appointment" to book directly without going through the patient portal.',
                'Confirmed appointments trigger an automatic reminder if email is configured.',
            ],
        ],
        'medical-records' => [
            'title' => 'Medical Records',
            'summary' => 'Document each consultation with progress notes, diagnoses, prescriptions, and file attachments.',
            'tips' => [
                'Each record is linked to the patient and the corresponding appointment.',
                'You can attach images, PDFs, and lab results.',
                'Records are private and only accessible by doctors and the owner.',
            ],
        ],
        'invoices' => [
            'title' => 'Invoices',
            'summary' => 'Create invoices, record payments, and track your clinic\'s income.',
            'tips' => [
                'You can create an invoice directly from a completed appointment.',
                'Statuses: Draft, Sent, Paid, Overdue, and Cancelled.',
                'Export the list as CSV for your accountant.',
            ],
        ],
        'prescriptions' => [
            'title' => 'Prescriptions',
            'summary' => 'Generate digital prescriptions linked to the patient and consultation record.',
            'tips' => [
                'Prescriptions include medication, dose, frequency, and duration.',
                'You can print or email the prescription to the patient.',
            ],
        ],
        'staff' => [
            'title' => 'Team',
            'summary' => 'Manage your clinic\'s users: invite members, assign roles, and control permissions.',
            'tips' => [
                'Available roles: Doctor, Assistant, Receptionist, and Secretary.',
                'Invite a new member using their email address.',
                'You can temporarily deactivate a user without deleting them.',
            ],
        ],
        'reports' => [
            'title' => 'Reports',
            'summary' => 'Analyse your clinic\'s performance with income, appointment, and occupancy reports.',
            'tips' => [
                'Filter reports by date range or by doctor.',
                'Income charts automatically compare month by month.',
            ],
        ],
        'schedule' => [
            'title' => 'Schedule',
            'summary' => 'Set up your clinic\'s opening days and hours for the online booking portal.',
            'tips' => [
                'You can define different hours for each day of the week.',
                'The schedule directly affects availability on the public portal.',
            ],
        ],
    ],

    // Tooltips — appointment statuses
    'tooltip' => [
        'appointment_pending' => 'The appointment was requested but not yet confirmed by the clinic.',
        'appointment_confirmed' => 'The appointment is confirmed. The patient will receive a reminder.',
        'appointment_in_progress' => 'The patient is currently being seen.',
        'appointment_completed' => 'The consultation ended successfully.',
        'appointment_cancelled' => 'The appointment was cancelled. It can be rescheduled.',
        'appointment_no_show' => 'The patient did not show up.',

        'record_type_consultation' => 'First visit or general review note.',
        'record_type_follow_up' => 'Follow-up for a previous treatment or diagnosis.',
        'record_type_procedure' => 'Clinical procedure or minor intervention.',
        'record_type_prescription' => 'Medical prescription issued.',

        'payment_cash' => 'Cash payment received at the clinic.',
        'payment_card' => 'Credit or debit card payment.',
        'payment_transfer' => 'Bank transfer.',
        'payment_insurance' => 'Covered by health insurance.',

        'role_owner' => 'Full access: settings, reports, billing, and team.',
        'role_doctor' => 'Access to patients, appointments, records, and prescriptions.',
        'role_assistant' => 'Clinical support: patients, appointments, and records.',
        'role_secretary' => 'Administrative management: appointments and patients.',
        'role_receptionist' => 'Reception: schedule appointments and register patients.',
    ],
];
