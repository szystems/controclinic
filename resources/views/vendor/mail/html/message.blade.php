<x-mail::layout>
{{-- Header: Logo ControClinic --}}
<x-slot:header>
@php
    $appLogoUrl  = \App\Models\AppSetting::get('branding.logo_url');
    $appName     = \App\Models\AppSetting::get('branding.app_name', config('app.name', 'ControClinic'));
@endphp
<x-mail::header :url="config('app.url')">
@if ($appLogoUrl)
<img src="{{ url($appLogoUrl) }}"
     alt="{{ $appName }}"
     style="display:block;margin:0 auto;height:48px;max-height:48px;width:auto;">
@else
<span style="font-size:22px;font-weight:bold;color:#18181b;letter-spacing:-0.5px;">{{ $appName }}</span>
@endif
</x-mail::header>
</x-slot:header>

{{-- Clinic sub-header (inyectado por emails dirigidos a pacientes) --}}
@isset($clinicHeader)
{!! $clinicHeader !!}
@endisset

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $appName }}. {{ __('mail.all_rights_reserved') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
