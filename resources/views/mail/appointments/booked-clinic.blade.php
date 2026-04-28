@php
    $date = $appointment->appointment_date->translatedFormat('l, d F Y');
    $time = \Carbon\Carbon::parse($appointment->start_time)->format('H:i');
@endphp
<x-mail::message>
# {{ __('appointments_mail.booked_clinic_title') }}

{{ __('appointments_mail.booked_clinic_intro', ['patient' => $patient->full_name]) }}

<x-mail::panel>
**{{ __('appointments_mail.label_reference') }}:** `{{ $reference }}`
**{{ __('appointments_mail.label_patient') }}:** {{ $patient->full_name }}
@if($patient->phone)
**{{ __('appointments_mail.label_phone') }}:** {{ $patient->phone }}
@endif
@if($patient->email)
**{{ __('appointments_mail.label_email') }}:** {{ $patient->email }}
@endif
**{{ __('appointments_mail.label_doctor') }}:** {{ $doctor->name }}
**{{ __('appointments_mail.label_date') }}:** {{ $date }}
**{{ __('appointments_mail.label_time') }}:** {{ $time }}
**{{ __('appointments_mail.label_status') }}:** {{ __('appointments.status_'.$appointment->status) }}
@if($appointment->reason)
**{{ __('appointments_mail.label_reason') }}:** {{ $appointment->reason }}
@endif
</x-mail::panel>

@if($requiresConfirmation)
{{ __('appointments_mail.booked_clinic_pending_note') }}
@endif

<x-mail::button :url="$manageUrl" color="primary">
{{ __('appointments_mail.manage_button') }}
</x-mail::button>

{{ __('general.thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>
