@extends('pdf._layout', ['title' => __('prescriptions.prescriptions') . ' ' . ($prescription->folio ?? $prescription->id)])

@section('content')
<style>
    .rx-header { display: table; width: 100%; margin-bottom: 14px; }
    .rx-left, .rx-right { display: table-cell; vertical-align: top; }
    .rx-right { text-align: right; }
    .rx-folio { font-size: 20px; font-weight: 700; color: #4f46e5; }
    .rx-meta { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; }
    .status-blue { background: #dbeafe; color: #1e40af; }
    .status-green { background: #dcfce7; color: #166534; }
    .status-gray { background: #f3f4f6; color: #374151; }
    .status-red { background: #fee2e2; color: #991b1b; }
    .section-label { font-size: 9px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; }
    .info-grid { display: table; width: 100%; }
    .info-col { display: table-cell; vertical-align: top; width: 50%; padding-right: 10px; }
    .rx-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 8px; }
    .rx-table th { background: #f3f4f6; color: #374151; font-size: 9px; font-weight: 600; text-align: left; padding: 5px 6px; border-bottom: 1px solid #d1d5db; }
    .rx-table td { padding: 6px 6px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
    .rx-table tr:last-child td { border-bottom: none; }
    .med-name { font-weight: 600; color: #111827; }
    .med-meta { color: #6b7280; font-size: 9px; margin-top: 2px; }
    .controlled-badge { display: inline-block; background: #fee2e2; color: #991b1b; border-radius: 4px; padding: 1px 5px; font-size: 8px; font-weight: 700; margin-left: 4px; }
    .footer-note { margin-top: 16px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .sig-box { display: table; width: 100%; margin-top: 24px; }
    .sig-cell { display: table-cell; text-align: center; width: 50%; }
    .sig-line { border-top: 1px solid #6b7280; margin: 0 20px; padding-top: 6px; font-size: 9px; color: #6b7280; }
</style>

{{-- Folio y estado (el layout ya muestra logo + nombre + contacto) --}}
<div style="display:table; width:100%; margin-bottom: 14px;">
    <div style="display:table-cell; vertical-align:middle;">
        <div class="rx-folio">{{ $prescription->folio ? 'Receta '.$prescription->folio : __('prescriptions.draft') }}</div>
        @php
            $statusColors = ['draft'=>'gray','issued'=>'blue','dispensed'=>'green','cancelled'=>'red'];
            $sc = $statusColors[$prescription->status] ?? 'gray';
        @endphp
        <div style="margin-top:4px;">
            <span class="status-badge status-{{ $sc }}">{{ $prescription->status_label }}</span>
        </div>
    </div>
    <div style="display:table-cell; vertical-align:middle; text-align:right;">
        <div class="rx-meta">{{ __('prescriptions.issued_at') }}: {{ $prescription->issued_at?->format('d/m/Y') ?? '—' }}</div>
        @if($prescription->valid_until)
        <div class="rx-meta">{{ __('prescriptions.valid_until') }}: {{ $prescription->valid_until->format('d/m/Y') }}</div>
        @endif
    </div>
</div>
<div class="info-box">
    <div class="info-grid">
        <div class="info-col">
            <div class="section-label">{{ __('prescriptions.patient') }}</div>
            <div style="font-size:11px; font-weight:600;">{{ $prescription->patient->full_name }}</div>
            @if($prescription->patient->birth_date)
            <div style="font-size:9px; color:#6b7280;">
                {{ __('patients.birth_date') }}: {{ $prescription->patient->birth_date->format('d/m/Y') }}
            </div>
            @endif
        </div>
        <div class="info-col" style="text-align:right;">
            <div class="section-label">{{ __('prescriptions.doctor') }}</div>
            <div style="font-size:11px; font-weight:600;">{{ $prescription->doctor?->name ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- Diagnóstico --}}
@if($prescription->diagnosis)
<div style="margin-bottom:10px;">
    <div class="section-label">{{ __('prescriptions.diagnosis') }}</div>
    <div style="font-size:10px;">{{ $prescription->diagnosis }}</div>
</div>
@endif

{{-- Medicamentos --}}
<div class="section-label" style="margin-bottom:4px;">{{ __('prescriptions.medications') }}</div>
@if($prescription->items->isNotEmpty())
<table class="rx-table">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('prescriptions.medication_name') }}</th>
            <th>{{ __('prescriptions.dose') }}</th>
            <th>{{ __('prescriptions.frequency') }}</th>
            <th>{{ __('prescriptions.duration') }}</th>
            <th>{{ __('prescriptions.quantity') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prescription->items as $item)
        <tr>
            <td>{{ $item->order + 1 }}</td>
            <td>
                <span class="med-name">{{ $item->medication_name }}</span>
                @if($item->is_controlled)<span class="controlled-badge">⚠ CTRL</span>@endif
                @if($item->presentation || $item->active_ingredient)
                <div class="med-meta">{{ implode(' · ', array_filter([$item->active_ingredient, $item->presentation])) }}</div>
                @endif
                @if($item->instructions)
                <div class="med-meta" style="font-style:italic;">{{ $item->instructions }}</div>
                @endif
            </td>
            <td>{{ $item->dose ?? '—' }}</td>
            <td>{{ $item->frequency ?? '—' }}</td>
            <td>{{ $item->duration ?? '—' }}</td>
            <td>{{ $item->quantity ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Instrucciones al paciente --}}
@if($prescription->notes)
<div style="margin-top:12px;">
    <div class="section-label">{{ __('prescriptions.notes') }}</div>
    <div style="font-size:10px; background:#fffbeb; border:1px solid #fef08a; border-radius:4px; padding:6px 8px;">
        {{ $prescription->notes }}
    </div>
</div>
@endif

{{-- Firmas --}}
<div class="sig-box">
    <div class="sig-cell">
        <div class="sig-line">{{ $prescription->doctor?->name ?? __('prescriptions.doctor') }}</div>
    </div>
    <div class="sig-cell">
        <div class="sig-line">{{ __('prescriptions.patient') }}: {{ $prescription->patient->full_name }}</div>
    </div>
</div>

<div class="footer-note">
    {{ $clinic->name }} — {{ $prescription->issued_at?->format('d \d\e F \d\e Y') ?? now()->format('d \d\e F \d\e Y') }}
    @if($prescription->folio) — {{ __('prescriptions.folio') }}: {{ $prescription->folio }} @endif
</div>
@endsection
