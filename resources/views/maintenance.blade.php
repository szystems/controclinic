<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('admin.maintenance_title') }} — {{ app_setting('branding.app_name', 'ControClinic') }}</title>
    @include('partials._head-branding')
</head>
<body style="margin:0;font-family:sans-serif;background:#f8fafc;color:#0f172a;">
    <main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
        <div style="max-width:32rem;text-align:center;">
            <h1 style="font-size:1.5rem;font-weight:600;">{{ __('admin.maintenance_title') }}</h1>
            <p style="margin-top:12px;color:#475569;">{{ __('admin.maintenance_body') }}</p>
            <a href="{{ route('login') }}" style="display:inline-block;margin-top:24px;color:#4f46e5;">
                {{ __('public.nav_login') }}
            </a>
        </div>
    </main>
</body>
</html>
