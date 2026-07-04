@props([
    'app' => null,
    'linkClass' => 'underline hover:opacity-80 transition-opacity',
])

@php
    $appName = $app ?? app_setting('branding.app_name', config('app.name', 'ControClinic'));
@endphp

<span {{ $attributes }}>
    {{ $appName }} · {{ __('public.product_of') }}
    <a href="https://szystems.com" target="_blank" rel="noopener noreferrer" class="{{ $linkClass }}">Szystems</a>
    · Victoria, BC
</span>
