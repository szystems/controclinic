<x-mail::message>

{{-- Clinic branding sub-header --}}
<x-slot:clinicHeader>
@include('mail.partials.clinic-header', ['clinic' => $clinic])
</x-slot:clinicHeader>

# {{ __('invitations.email_greeting', ['name' => $invitation->name]) }}

{{ __('invitations.email_body', ['inviter' => $inviterName, 'clinic' => $clinicName]) }}

**{{ __('staff.role') }}:** {{ $roleName }}

<x-mail::button :url="$acceptUrl" color="primary">
{{ __('invitations.email_accept_button') }}
</x-mail::button>

{{ __('invitations.email_expires', ['date' => $expiresAt->format('d/m/Y H:i')]) }}

{{ __('invitations.email_ignore') }}

{{ __('general.thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>
