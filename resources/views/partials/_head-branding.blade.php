@php
    $appName    = \App\Models\AppSetting::get('branding.app_name', config('app.name', 'ControClinic'));
    $faviconUrl = \App\Models\AppSetting::get('branding.favicon_url');
@endphp
@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
@else
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@endif
