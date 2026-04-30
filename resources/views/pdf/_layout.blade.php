<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        @page {
            margin: 18mm 12mm 22mm 12mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        h1, h2, h3, h4 { margin: 0; padding: 0; color: #111827; }
        h1 { font-size: 16px; font-weight: 700; }
        h2 { font-size: 12px; font-weight: 600; }
        h3 { font-size: 11px; font-weight: 600; }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-left, .header-center, .header-right {
            display: table-cell;
            vertical-align: middle;
        }
        .header-left {
            width: 140px;
        }
        .header-center {
            text-align: center;
            padding: 0 14px;
        }
        .header-right { text-align: right; font-size: 9px; color: #6b7280; }
        .header .clinic-name { font-size: 14px; font-weight: 700; color: #111827; }
        .header .clinic-subtitle { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .header .clinic-contact { font-size: 9px; color: #9ca3af; margin-top: 1px; }
        .header img.logo { height: 38px; width: auto; max-width: 130px; vertical-align: middle; }

        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
        .footer .pagenum:before { content: counter(page); }
        .footer .pages:before { content: counter(pages); }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            text-align: left;
            padding: 5px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        th {
            font-size: 9px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            background: #f9fafb;
            border-bottom: 1.5px solid #d1d5db;
        }
        tr.zebra td { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #6b7280; }
        .small { font-size: 9px; }

        .meta {
            display: table; width: 100%; margin-bottom: 10px;
            background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px;
            padding: 6px 8px;
        }
        .meta-item { display: inline-block; margin-right: 16px; font-size: 9px; }
        .meta-item strong { color: #111827; }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            background: #eef2ff;
            color: #4338ca;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }
        .card .label { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; }
        .card .value { font-size: 11px; font-weight: 600; color: #111827; margin-top: 2px; }

        .grid-2 { display: table; width: 100%; }
        .grid-2 > * { display: table-cell; width: 50%; vertical-align: top; padding-right: 6px; }
        .grid-3 { display: table; width: 100%; }
        .grid-3 > * { display: table-cell; width: 33.33%; vertical-align: top; padding-right: 6px; }

        .section-title {
            font-size: 11px; font-weight: 700; color: #111827;
            border-left: 3px solid #6366f1; padding-left: 6px;
            margin: 10px 0 6px;
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $clinicLogo = isset($clinic) && ($clinic->branding['logo'] ?? null);
        $logoPath = null;
        if ($clinicLogo) {
            $candidate = storage_path('app/public/'.ltrim($clinic->branding['logo'], '/'));
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }
        $contact = isset($clinic) ? collect([$clinic->phone ?? null, $clinic->email ?? null])->filter()->implode(' · ') : '';
    @endphp

    <div class="header">
        <div class="header-left">
            @if($logoPath)
                <img class="logo" src="{{ $logoPath }}" alt="">
            @endif
        </div>
        <div class="header-center">
            <div class="clinic-name">{{ $clinic->name ?? config('app.name') }}</div>
            @if(!empty($title))
                <div class="clinic-subtitle">{{ $title }}</div>
            @endif
            @if($contact)
                <div class="clinic-contact">{{ $contact }}</div>
            @endif
        </div>
        <div class="header-right">
            <div><strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
            @if(!empty($subheader))
                <div>{{ $subheader }}</div>
            @endif
        </div>
    </div>

    @yield('content')

    <div class="footer">
        {{ $clinic->name ?? config('app.name') }} ·
        {{ __('general.generated_with') }} ControClinic ·
        {{ __('general.page') }} <span class="pagenum"></span> / <span class="pages"></span>
    </div>
</body>
</html>
