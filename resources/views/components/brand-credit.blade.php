@props([
    'app' => null,
    'linkClass' => 'underline hover:opacity-80 transition-opacity',
])

@php
    $appName = $app ?? app_setting('branding.app_name', config('app.name', 'ControClinic'));
@endphp

<span {{ $attributes }}>
    {{ $appName }} · {{ __('public.product_of') }}
    <a href="https://szystems.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 align-middle">
        <img src="{{ asset('images/szystems-mark.png') }}" alt="" width="14" height="14" class="h-3.5 w-3.5 shrink-0" style="height:14px;width:14px;vertical-align:middle;border:0;">
        <span class="{{ $linkClass }}">Szystems</span>
    </a>
    · Victoria, BC
</span>
