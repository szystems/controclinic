<?php

return [
    // Títulos
    'title' => 'Facturación',
    'invoice' => 'Factura',
    'invoices' => 'Facturas',
    'new_invoice' => 'Nueva Factura',
    'invoice_details' => 'Detalles de la Factura',
    'edit_invoice' => 'Editar Factura',
    'record_payment' => 'Registrar Pago',
    'payments' => 'Pagos',
    'items' => 'Conceptos',
    'add_item' => 'Agregar Concepto',
    'remove_item' => 'Eliminar',

    // Campos
    'invoice_number' => 'N° Factura',
    'issued_at' => 'Fecha de Emisión',
    'due_at' => 'Fecha Límite de Pago',
    'patient' => 'Paciente',
    'doctor' => 'Doctor',
    'appointment' => 'Cita Relacionada',
    'currency' => 'Moneda',
    'notes' => 'Notas',
    'subtotal' => 'Subtotal',
    'discount' => 'Descuento',
    'tax' => 'Impuesto',
    'total' => 'Total',
    'paid' => 'Pagado',
    'balance' => 'Saldo Pendiente',

    // Estados
    'status' => 'Estado',
    'status_draft' => 'Borrador',
    'status_pending' => 'Pendiente',
    'status_partial' => 'Pago Parcial',
    'status_paid' => 'Pagada',
    'status_refunded' => 'Reembolsada',
    'status_cancelled' => 'Cancelada',
    'all_statuses' => 'Todos los estados',

    // Tipos de ítem
    'item_description' => 'Descripción',
    'item_quantity' => 'Cantidad',
    'item_unit_price' => 'Precio Unitario',
    'item_discount' => 'Descuento',
    'item_tax_rate' => 'Impuesto (%)',
    'item_total' => 'Total',
    'item_type' => 'Tipo',
    'item_type_consultation' => 'Consulta',
    'item_type_procedure' => 'Procedimiento',
    'item_type_medication' => 'Medicamento',
    'item_type_lab' => 'Laboratorio',
    'item_type_other' => 'Otro',

    // Métodos de pago
    'payment_amount' => 'Monto',
    'payment_method' => 'Método de Pago',
    'payment_method_cash' => 'Efectivo',
    'payment_method_card' => 'Tarjeta',
    'payment_method_transfer' => 'Transferencia',
    'payment_method_insurance' => 'Seguro',
    'payment_method_other' => 'Otro',
    'payment_reference' => 'Referencia / N° Transacción',
    'payment_notes' => 'Notas del Pago',
    'payment_date' => 'Fecha del Pago',
    'payment_recorded' => 'Pago registrado correctamente',

    // Acciones
    'generate_from_appointment' => 'Generar Factura',
    'mark_as_cancelled' => 'Cancelar Factura',
    'confirm_cancel' => '¿Cancelar esta factura? Esta acción no se puede deshacer si ya tiene pagos.',
    'print_invoice' => 'Imprimir',
    'export_pdf' => 'Exportar PDF',

    // Mensajes
    'invoice_created' => 'Factura creada correctamente',
    'invoice_updated' => 'Factura actualizada',
    'invoice_cancelled' => 'Factura cancelada',
    'invoice_paid' => 'Factura marcada como pagada',
    'cannot_edit_paid' => 'No se puede editar una factura pagada o cancelada',
    'no_invoices' => 'No hay facturas aún',
    'no_invoices_desc' => 'Las facturas se crean al generar un comprobante de pago por consulta.',

    // Configuración de facturación
    'billing_settings' => 'Configuración de Facturación',
    'billing_enabled' => 'Facturación habilitada',
    'billing_disabled_hint' => 'Habilita la facturación en Configuración > Facturación para usar este módulo.',
    'invoice_prefix' => 'Prefijo de Factura',
    'tax_rate' => 'Tasa de Impuesto (%)',
    'tax_label' => 'Nombre del Impuesto (IVA, ITBMS, IGV…)',
    'tax_included' => 'Impuesto incluido en el precio',
    'default_price' => 'Precio de Consulta por Defecto',
    'invoice_footer' => 'Pie de Página en Facturas',

    // PDF
    'pdf_title' => 'Comprobante de Pago',
    'pdf_issued_by' => 'Emitido por',
    'pdf_patient' => 'Paciente',
    'pdf_payment_history' => 'Historial de Pagos',

    // UI extras
    'item' => 'Línea',
    'no_doctors' => 'No hay doctores registrados en la clínica.',
    'from_appointment' => 'Desde cita',
    'patient_search_hint' => 'Escribe al menos 2 caracteres para buscar',
    'create_invoice' => 'Generar factura',
    'view_invoice' => 'Ver factura',
];
