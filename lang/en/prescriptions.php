<?php

return [
    // Titles
    'prescriptions'        => 'Prescriptions',
    'new_prescription'     => 'New Prescription',
    'edit_prescription'    => 'Edit Prescription',
    'prescription_detail'  => 'Prescription Detail',
    'draft'                => 'Draft',

    // Statuses
    'status'               => 'Status',
    'all_statuses'         => 'All statuses',
    'status_draft'         => 'Draft',
    'status_issued'        => 'Issued',
    'status_dispensed'     => 'Dispensed',
    'status_cancelled'     => 'Cancelled',

    // Fields
    'folio'                => 'Folio',
    'patient'              => 'Patient',
    'doctor'               => 'Doctor',
    'diagnosis'            => 'Diagnosis',
    'diagnosis_placeholder' => 'e.g. Acute respiratory infection (ICD-10: J06.9)',
    'notes'                => 'Patient instructions',
    'notes_placeholder'    => 'General instructions, warnings, follow-up...',
    'internal_notes'       => 'Internal notes',
    'internal_notes_hint'  => 'Only visible to authorized staff',
    'issued_at'            => 'Issued at',
    'valid_until'          => 'Valid until',
    'expired'              => 'Expired',
    'from_record'          => 'Related consultation',
    'view_record'          => 'View consultation',

    // Medications
    'medications'          => 'Medications',
    'medication'           => 'Medication',
    'no_medications'       => 'No medications recorded.',
    'add_medication'       => 'Add medication',
    'medication_name'      => 'Medication name',
    'medication_name_placeholder' => 'e.g. Ibuprofen 400mg / Advil 400mg',
    'active_ingredient'    => 'Active ingredient',
    'presentation'         => 'Presentation',
    'presentation_placeholder' => 'e.g. tablets 400mg',
    'dose'                 => 'Dose',
    'dose_placeholder'     => 'e.g. 1 tablet',
    'frequency'            => 'Frequency',
    'frequency_placeholder' => 'e.g. every 8 hours',
    'duration'             => 'Duration',
    'duration_placeholder' => 'e.g. 7 days',
    'route'                => 'Route',
    'route_oral'           => 'Oral',
    'route_topical'        => 'Topical',
    'route_injectable'     => 'Injectable',
    'route_inhalation'     => 'Inhalation',
    'route_sublingual'     => 'Sublingual',
    'route_ophthalmic'     => 'Ophthalmic',
    'route_otic'           => 'Otic',
    'route_rectal'         => 'Rectal',
    'route_other'          => 'Other',
    'instructions'         => 'Instructions',
    'instructions_placeholder' => 'e.g. Take with food, avoid sun exposure',
    'quantity'             => 'Quantity',
    'is_controlled'        => 'Controlled substance',
    'controlled'           => 'Controlled',

    // Sections
    'general_data'         => 'General data',
    'select_patient'              => 'Select patient',
    'search_patient'              => 'Search by patient',
    'search_patient_placeholder'  => 'Search by name, phone, email or ID',
    'no_patient_found'            => 'No patients found matching that search',

    // Actions
    'save_draft'           => 'Save draft',
    'issue_prescription'   => 'Issue prescription',
    'cancel_prescription'  => 'Cancel prescription',
    'confirm_cancel_message' => 'Are you sure you want to cancel this prescription? This action cannot be undone.',
    'yes_cancel'           => 'Yes, cancel',

    // Messages
    'created_successfully' => 'Prescription created successfully.',
    'updated_successfully' => 'Prescription updated.',
    'issued_successfully'  => 'Prescription issued.',
    'cancelled_successfully' => 'Prescription cancelled.',
    'no_prescriptions'              => 'No prescriptions found.',
    'no_prescriptions_description'  => 'Issue your first prescription and deliver it with a QR verification code.',
    'empty_state_bullet_1'          => 'Medication items with dose, frequency and duration',
    'empty_state_bullet_2'          => 'Printable PDF with public QR verification code',
    'empty_state_bullet_3'          => 'Automatically linked to the patient\'s medical record',

    // QR (Phase 2)
    'verify_prescription'  => 'Verify prescription',
    'qr_valid'             => 'Valid prescription',
    'qr_invalid'           => 'Prescription not found or invalid',
];
