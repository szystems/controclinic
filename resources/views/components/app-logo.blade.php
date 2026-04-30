@php
    $logoUrl = \App\Models\AppSetting::get('branding.logo_url');
    $appName = \App\Models\AppSetting::get('branding.app_name', config('app.name', 'ControClinic'));
@endphp

@if($logoUrl)
    <img
        src="{{ $logoUrl }}"
        alt="{{ $appName }}"
        {{ $attributes->merge(['class' => 'block h-9 w-auto object-contain']) }}
    />
@else
    <x-application-logo {{ $attributes }} />
@endif
