@php
    $date = $appointment->appointment_date->translatedFormat('l, d F Y');
    $time = \Carbon\Carbon::parse($appointment->start_time)->format('H:i');
@endphp
<x-mail::message>
# {{ __('appointments_mail.greeting', ['name' => $patient->first_name]) }}

{{ __('appointments_mail.confirmed_intro', ['clinic' => $clinic->name]) }}

<x-mail::panel>
**{{ __('appointments_mail.label_reference') }}:** `{{ $reference }}`
**{{ __('appointments_mail.label_doctor') }}:** {{ $doctor->name }}
**{{ __('appointments_mail.label_date') }}:** {{ $date }}
**{{ __('appointments_mail.label_time') }}:** {{ $time }}
</x-mail::panel>

{{ __('appointments_mail.confirmed_note') }}

{{ __('general.thanks') }},<br>
{{ $clinic->name }}
</x-mail::message>
