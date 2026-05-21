@php
    /** @var \App\Models\Invoice $invoice */
    $currency    = $invoice->currency ?? $invoice->clinic->currency ?? 'USD';
    $clinicSettings = $invoice->clinic->settings ?? [];
    $taxLabel    = $clinicSettings['tax_label'] ?? 'IVA';
@endphp
<x-mail::message>

{{-- Clinic branding sub-header --}}
<x-slot:clinicHeader>
@include('mail.partials.clinic-header', ['clinic' => $clinic])
</x-slot:clinicHeader>

# {{ __('invoices_mail.receipt_title') }}

{{ __('invoices_mail.receipt_greeting', ['name' => $patient->first_name ?? $patient->full_name]) }}

{{ __('invoices_mail.receipt_intro', ['clinic' => $clinic->name, 'number' => $invoice->invoice_number]) }}

<x-mail::panel>
**{{ __('invoices_mail.label_number') }}:** `{{ $invoice->invoice_number }}`
**{{ __('invoices_mail.label_date') }}:** {{ $invoice->issued_at->translatedFormat('d F Y') }}
@if($doctor)
**{{ __('invoices_mail.label_doctor') }}:** {{ $doctor->name }}
@endif
**{{ __('invoices_mail.label_status') }}:** {{ __('invoices.status_'.$invoice->status) }}
</x-mail::panel>

**{{ __('invoices_mail.label_items') }}:**

<x-mail::table>
| {{ __('invoices_mail.col_description') }} | {{ __('invoices_mail.col_qty') }} | {{ __('invoices_mail.col_unit_price') }} | {{ __('invoices_mail.col_total') }} |
|:---|:---:|---:|---:|
@foreach($items as $item)
| {{ $item->description }} | {{ $item->quantity }} | {{ $currency }} {{ number_format($item->unit_price, 2) }} | {{ $currency }} {{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>

<x-mail::panel>
@if((float)$invoice->discount_amount > 0)
**{{ __('invoices_mail.label_subtotal') }}:** {{ $currency }} {{ number_format($invoice->subtotal, 2) }}
**{{ __('invoices_mail.label_discount') }}:** − {{ $currency }} {{ number_format($invoice->discount_amount, 2) }}
@endif
@if((float)$invoice->tax_amount > 0)
**{{ $taxLabel }}:** {{ $currency }} {{ number_format($invoice->tax_amount, 2) }}
@endif
**{{ __('invoices_mail.label_total') }}:** {{ $currency }} {{ number_format($invoice->total, 2) }}
</x-mail::panel>

{{ __('invoices_mail.receipt_footer_note', ['clinic' => $clinic->name]) }}

{{ __('general.thanks') }},<br>
{{ $clinic->name }}
</x-mail::message>
