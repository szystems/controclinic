@extends('pdf._layout', ['title' => __('invoices.pdf_title') . ' ' . $invoice->invoice_number])

@section('content')
<style>
    .inv-header-grid { display: table; width: 100%; margin-bottom: 14px; }
    .inv-header-left, .inv-header-right { display: table-cell; vertical-align: top; }
    .inv-header-right { text-align: right; }
    .inv-number { font-size: 22px; font-weight: 700; color: #4f46e5; }
    .inv-meta { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; }
    .status-green { background: #dcfce7; color: #166534; }
    .status-yellow { background: #fef9c3; color: #854d0e; }
    .status-blue { background: #dbeafe; color: #1e40af; }
    .status-red { background: #fee2e2; color: #991b1b; }
    .status-gray { background: #f3f4f6; color: #374151; }
    .status-purple { background: #ede9fe; color: #5b21b6; }
    .totals-table { width: auto; margin-left: auto; min-width: 200px; }
    .totals-table td { border: none; padding: 3px 6px; }
    .totals-table .total-row td { border-top: 1.5px solid #d1d5db; font-weight: 700; font-size: 12px; color: #111827; padding-top: 5px; }
    .payments-section { margin-top: 16px; }
    .footer-note { margin-top: 20px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .section-label { font-size: 9px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; }
    .info-grid { display: table; width: 100%; }
    .info-col { display: table-cell; vertical-align: top; width: 50%; padding-right: 10px; }
</style>

{{-- Encabezado de la factura --}}
<div class="inv-header-grid">
    <div class="inv-header-left">
        <div class="section-label">{{ __('invoices.pdf_issued_by') }}</div>
        <div style="font-size:12px; font-weight:700;">{{ $clinic->name }}</div>
        @if($clinic->address)
        <div style="font-size:9px; color:#6b7280;">{{ $clinic->address }}</div>
        @endif
        @if($clinic->phone)
        <div style="font-size:9px; color:#6b7280;">{{ $clinic->phone }}</div>
        @endif
    </div>
    <div class="inv-header-right">
        <div class="inv-number">{{ __('invoices.invoice') }} #{{ $invoice->invoice_number }}</div>
        <div class="inv-meta">{{ __('invoices.issued_at') }}: {{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y') }}</div>
        @if($invoice->due_at)
        <div class="inv-meta">{{ __('invoices.due_at') }}: {{ \Carbon\Carbon::parse($invoice->due_at)->format('d/m/Y') }}</div>
        @endif
        <div style="margin-top:4px;">
            @php
                $statusClass = match($invoice->status) {
                    'paid'      => 'status-green',
                    'partial'   => 'status-yellow',
                    'pending'   => 'status-blue',
                    'draft'     => 'status-gray',
                    'cancelled' => 'status-red',
                    'refunded'  => 'status-purple',
                    default     => 'status-gray',
                };
            @endphp
            <span class="status-badge {{ $statusClass }}">{{ $invoice->status_label }}</span>
        </div>
    </div>
</div>

{{-- Datos del paciente --}}
<div class="info-box">
    <div class="info-grid">
        <div class="info-col">
            <div class="section-label">{{ __('invoices.pdf_patient') }}</div>
            <div style="font-weight:600;">{{ $invoice->patient->full_name ?? '—' }}</div>
            @if($invoice->patient?->document_number)
            <div style="font-size:9px; color:#6b7280;">{{ $invoice->patient->document_number }}</div>
            @endif
            @if($invoice->patient?->email)
            <div style="font-size:9px; color:#6b7280;">{{ $invoice->patient->email }}</div>
            @endif
        </div>
        @if($invoice->doctor)
        <div class="info-col">
            <div class="section-label">{{ __('invoices.doctor') }}</div>
            <div style="font-weight:600;">{{ $invoice->doctor->name }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Ítems --}}
<table>
    <thead>
        <tr>
            <th style="width:40%">{{ __('invoices.item_description') }}</th>
            <th style="width:10%" class="text-center">{{ __('invoices.item_quantity') }}</th>
            <th style="width:15%" class="text-right">{{ __('invoices.item_unit_price') }}</th>
            <th style="width:10%" class="text-right">{{ __('invoices.item_discount') }}</th>
            <th style="width:10%" class="text-right">{{ __('invoices.item_tax_rate') }}</th>
            <th style="width:15%" class="text-right">{{ __('invoices.item_total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items->sortBy('order') as $item)
        <tr>
            <td>
                <div>{{ $item->description }}</div>
                <div style="font-size:8px; color:#9ca3af;">{{ $item->type_label }}</div>
            </td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
            <td class="text-right">{{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '—' }}</td>
            <td class="text-right">{{ $item->tax_rate > 0 ? $item->tax_rate . '%' : '—' }}</td>
            <td class="text-right" style="font-weight:600;">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totales --}}
<table class="totals-table">
    <tr>
        <td class="text-right" style="color:#6b7280;">{{ __('invoices.subtotal') }}</td>
        <td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td>
    </tr>
    @if($invoice->discount_amount > 0)
    <tr>
        <td class="text-right" style="color:#6b7280;">{{ __('invoices.discount') }}</td>
        <td class="text-right" style="color:#dc2626;">-{{ number_format($invoice->discount_amount, 2) }}</td>
    </tr>
    @endif
    @if($invoice->tax_amount > 0)
    <tr>
        <td class="text-right" style="color:#6b7280;">{{ __('invoices.tax') }}</td>
        <td class="text-right">{{ number_format($invoice->tax_amount, 2) }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td class="text-right">{{ __('invoices.total') }}</td>
        <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
    </tr>
    @if($invoice->paid_amount > 0)
    <tr>
        <td class="text-right" style="color:#6b7280;">{{ __('invoices.paid') }}</td>
        <td class="text-right" style="color:#16a34a;">{{ number_format($invoice->paid_amount, 2) }}</td>
    </tr>
    @endif
    @if($invoice->balance > 0)
    <tr>
        <td class="text-right" style="color:#dc2626; font-weight:700;">{{ __('invoices.balance') }}</td>
        <td class="text-right" style="color:#dc2626; font-weight:700;">{{ number_format($invoice->balance, 2) }}</td>
    </tr>
    @endif
</table>

{{-- Historial de pagos --}}
@if($invoice->payments->isNotEmpty())
<div class="payments-section">
    <h3>{{ __('invoices.pdf_payment_history') }}</h3>
    <table style="margin-top:6px;">
        <thead>
            <tr>
                <th>{{ __('invoices.payment_date') }}</th>
                <th>{{ __('invoices.payment_method') }}</th>
                <th>{{ __('invoices.payment_reference') }}</th>
                <th class="text-right">{{ __('invoices.payment_amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments->sortBy('paid_at') as $payment)
            <tr>
                <td>{{ $payment->paid_at->format('d/m/Y') }}</td>
                <td>{{ $payment->method_label }}</td>
                <td>{{ $payment->reference ?? '—' }}</td>
                <td class="text-right" style="font-weight:600;">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Notas + pie de página --}}
@if($invoice->notes || !empty($clinic->settings['invoice_footer_text']))
<div class="footer-note">
    @if($invoice->notes)
    <p><strong>{{ __('invoices.notes') }}:</strong> {{ $invoice->notes }}</p>
    @endif
    @if(!empty($clinic->settings['invoice_footer_text']))
    <p>{{ $clinic->settings['invoice_footer_text'] }}</p>
    @endif
</div>
@endif
@endsection
