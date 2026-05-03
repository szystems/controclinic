<?php

return [
    // Titles
    'title' => 'Billing',
    'invoice' => 'Invoice',
    'invoices' => 'Invoices',
    'new_invoice' => 'New Invoice',
    'invoice_details' => 'Invoice Details',
    'edit_invoice' => 'Edit Invoice',
    'record_payment' => 'Record Payment',
    'payments' => 'Payments',
    'items' => 'Line Items',
    'add_item' => 'Add Item',
    'remove_item' => 'Remove',

    // Fields
    'invoice_number' => 'Invoice #',
    'issued_at' => 'Issue Date',
    'due_at' => 'Due Date',
    'patient' => 'Patient',
    'doctor' => 'Doctor',
    'appointment' => 'Related Appointment',
    'currency' => 'Currency',
    'notes' => 'Notes',
    'subtotal' => 'Subtotal',
    'discount' => 'Discount',
    'tax' => 'Tax',
    'total' => 'Total',
    'paid' => 'Paid',
    'balance' => 'Balance Due',

    // Statuses
    'status' => 'Status',
    'status_draft' => 'Draft',
    'status_pending' => 'Pending',
    'status_partial' => 'Partially Paid',
    'status_paid' => 'Paid',
    'status_refunded' => 'Refunded',
    'status_cancelled' => 'Cancelled',
    'all_statuses' => 'All statuses',

    // Item types
    'item_description' => 'Description',
    'item_quantity' => 'Quantity',
    'item_unit_price' => 'Unit Price',
    'item_discount' => 'Discount',
    'item_tax_rate' => 'Tax Rate (%)',
    'item_total' => 'Total',
    'item_type' => 'Type',
    'item_type_consultation' => 'Consultation',
    'item_type_procedure' => 'Procedure',
    'item_type_medication' => 'Medication',
    'item_type_lab' => 'Lab',
    'item_type_other' => 'Other',

    // Payment methods
    'payment_amount' => 'Amount',
    'payment_method' => 'Payment Method',
    'payment_method_cash' => 'Cash',
    'payment_method_card' => 'Card',
    'payment_method_transfer' => 'Bank Transfer',
    'payment_method_insurance' => 'Insurance',
    'payment_method_other' => 'Other',
    'payment_reference' => 'Reference / Transaction #',
    'payment_notes' => 'Payment Notes',
    'payment_date' => 'Payment Date',
    'payment_recorded' => 'Payment recorded successfully',

    // Actions
    'generate_from_appointment' => 'Generate Invoice',
    'mark_as_cancelled' => 'Cancel Invoice',
    'confirm_cancel' => 'Cancel this invoice? This cannot be undone if payments have been recorded.',
    'print_invoice' => 'Print',
    'export_pdf' => 'Export PDF',

    // Messages
    'invoice_created' => 'Invoice created successfully',
    'invoice_updated' => 'Invoice updated',
    'invoice_cancelled' => 'Invoice cancelled',
    'invoice_paid' => 'Invoice marked as paid',
    'cannot_edit_paid' => 'Cannot edit a paid or cancelled invoice',
    'no_invoices' => 'No invoices yet',
    'no_invoices_desc' => 'Invoices are created by generating a payment receipt for a consultation.',

    // Billing settings
    'billing_settings' => 'Billing Settings',
    'billing_enabled' => 'Billing enabled',
    'billing_disabled_hint' => 'Enable billing in Settings > Billing to use this module.',
    'invoice_prefix' => 'Invoice Prefix',
    'tax_rate' => 'Tax Rate (%)',
    'tax_label' => 'Tax Label (VAT, GST…)',
    'tax_included' => 'Tax included in price',
    'default_price' => 'Default Consultation Price',
    'invoice_footer' => 'Invoice Footer Text',

    // PDF
    'pdf_title' => 'Payment Receipt',
    'pdf_issued_by' => 'Issued by',
    'pdf_patient' => 'Patient',
    'pdf_payment_history' => 'Payment History',

    // UI extras
    'item' => 'Line',
    'no_doctors' => 'No doctors registered in this clinic.',
    'from_appointment' => 'From appointment',
    'patient_search_hint' => 'Type at least 2 characters to search',
    'create_invoice' => 'Create invoice',
    'view_invoice' => 'View invoice',
    'view_cancelled_invoice' => 'View cancelled invoice',
    'edit_invoice' => 'Edit invoice',
    'patient_locked' => 'Not editable',
    'delete_payment' => 'Void payment',
    'confirm_delete_payment' => 'Void this payment? The invoice balance will be recalculated.',
    'payment_deleted' => 'Payment voided successfully.',];
