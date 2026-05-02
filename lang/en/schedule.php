<?php

return [
    'title' => 'My schedule',
    'subtitle' => 'Manage your schedule blocks, vacations and unavailable times',
    'new_block' => 'New block',
    'no_blocks' => 'No schedule blocks registered',
    'no_blocks_desc' => 'Add a block to mark days or hours when you are not available',

    // Form
    'form_title_create' => 'Add schedule block',
    'form_title_edit' => 'Edit block',
    'date_from' => 'From date',
    'date_to' => 'To date',
    'all_day' => 'All day',
    'partial_hours' => 'Partial block (hours)',
    'time_from' => 'From time',
    'time_to' => 'To time',
    'reason' => 'Reason (optional)',
    'reason_placeholder' => 'E.g.: Vacation, Medical congress, External consultation…',

    // Actions
    'save' => 'Save block',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'confirm_delete' => 'Delete this schedule block?',

    // Messages
    'created' => 'Schedule block created.',
    'updated' => 'Schedule block updated.',
    'deleted' => 'Schedule block deleted.',

    // Conflict in appointment
    'doctor_unavailable' => 'The doctor is not available on the selected date/time.',

    // Labels in lists
    'all_day_label' => 'All day',
    'partial_label' => ':from – :to',

    // Validation
    'date_to_after' => 'The end date must be equal to or after the start date.',
    'time_to_after' => 'The end time must be after the start time.',

    // Doctor selector (owner/admin view)
    'doctor_label' => 'Doctor',
    'select_doctor' => 'Select a doctor',
    'managing_for' => 'Managing schedule for :name',
];
