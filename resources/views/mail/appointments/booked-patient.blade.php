@php
    /** @var \App\Models\Appointment $appointment */
    $date = $appointment->appointment_date->translatedFormat('l, d F Y');
    $time = \Carbon\Carbon::parse($appointment->start_time)->format('H:i');
@endphp
<x-mail::message>

{{-- Clinic branding sub-header --}}
<x-slot:clinicHeader>
@include('mail.partials.clinic-header', ['clinic' => $clinic])
</x-slot:clinicHeader>
# {{ __('appointments_mail.greeting', ['name' => $patient->first_name]) }}

@if($requiresConfirmation)
{{ __('appointments_mail.booked_patient_pending_intro', ['clinic' => $clinic->name]) }}
@else
{{ __('appointments_mail.booked_patient_confirmed_intro', ['clinic' => $clinic->name]) }}
@endif

<x-mail::panel>
**{{ __('appointments_mail.label_reference') }}:** `{{ $reference }}`
**{{ __('appointments_mail.label_doctor') }}:** {{ $doctor->name }}
**{{ __('appointments_mail.label_date') }}:** {{ $date }}
**{{ __('appointments_mail.label_time') }}:** {{ $time }}
@if($appointment->reason)
**{{ __('appointments_mail.label_reason') }}:** {{ $appointment->reason }}
@endif
</x-mail::panel>

@if($requiresConfirmation)
{{ __('appointments_mail.booked_patient_pending_note') }}
@else
{{ __('appointments_mail.booked_patient_confirmed_note') }}
@endif

@if($appointment->confirmation_token)
@if($appointment->status === 'scheduled')
<x-mail::table>
| | |
|:---:|:---:|
| <x-mail::button :url="route('appointment.confirm', $appointment->confirmation_token)" color="success">{{ __('appointments_mail.btn_confirm') }}</x-mail::button> | <x-mail::button :url="route('appointment.cancel', $appointment->confirmation_token)" color="error">{{ __('appointments_mail.btn_cancel') }}</x-mail::button> |
</x-mail::table>
@else
<x-mail::button :url="route('appointment.cancel', $appointment->confirmation_token)" color="error">
{{ __('appointments_mail.btn_cancel') }}
</x-mail::button>
@endif
@endif

@if($clinic->address || $clinic->phone)
**{{ __('appointments_mail.clinic_info') }}:**
{{ $clinic->name }}
@if($clinic->address){{ $clinic->address }}@endif
@if($clinic->phone){{ "\n".$clinic->phone }}@endif
@endif

{{ __('appointments_mail.cancellation_note') }}

{{ __('general.thanks') }},<br>
{{ $clinic->name }}
</x-mail::message>
