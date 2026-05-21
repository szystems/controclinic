@php
    $date = $appointment->appointment_date->translatedFormat('l, d F Y');
    $time = \Carbon\Carbon::parse($appointment->start_time)->format('H:i');
@endphp
<x-mail::message>

{{-- Clinic branding sub-header --}}
<x-slot:clinicHeader>
@include('mail.partials.clinic-header', ['clinic' => $clinic])
</x-slot:clinicHeader>

# {{ __('appointments_mail.greeting', ['name' => $patient->first_name]) }}

{{ __('appointments_mail.cancelled_intro', ['clinic' => $clinic->name]) }}

<x-mail::panel>
**{{ __('appointments_mail.label_reference') }}:** `{{ $reference }}`
**{{ __('appointments_mail.label_doctor') }}:** {{ $doctor->name }}
**{{ __('appointments_mail.label_date') }}:** {{ $date }}
**{{ __('appointments_mail.label_time') }}:** {{ $time }}
@if($reason)
**{{ __('appointments_mail.label_cancellation_reason') }}:** {{ $reason }}
@endif
</x-mail::panel>

{{ __('appointments_mail.cancelled_note', ['clinic' => $clinic->name]) }}

{{ __('general.thanks') }},<br>
{{ $clinic->name }}
</x-mail::message>
