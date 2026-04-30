@extends('pdf._layout', [
    'title' => __('appointments.appointment_voucher'),
    'subheader' => $appointment->appointment_date?->format('d/m/Y'),
])

@push('styles')
<style>
    .voucher-banner {
        background: #4f46e5;
        color: #fff;
        padding: 14px 16px;
        border-radius: 6px;
        margin-bottom: 12px;
    }
    .voucher-banner .label { font-size: 9px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px; }
    .voucher-banner .value { font-size: 22px; font-weight: 700; margin-top: 2px; }
    .info-row { margin-bottom: 6px; font-size: 10px; }
    .info-row .label { color: #6b7280; display: inline-block; min-width: 110px; }
    .info-row .value { color: #111827; font-weight: 600; }
</style>
@endpush

@section('content')
    <div class="voucher-banner">
        <div class="label">{{ __('appointments.appointment_voucher') }}</div>
        <div class="value">
            {{ $appointment->appointment_date?->format('d/m/Y') }}
            @if($appointment->start_time)
                · {{ $appointment->start_time?->format('H:i') }}
            @endif
        </div>
        @if($appointment->queue_number)
            <div class="label" style="margin-top:6px;">{{ __('appointments.queue_number') }}</div>
            <div class="value" style="font-size:18px;">#{{ $appointment->queue_number }}</div>
        @endif
    </div>

    <div class="grid-2">
        <div>
            <div class="section-title">{{ __('appointments.patient') }}</div>
            <div class="info-row"><span class="label">{{ __('patients.full_name') }}:</span>
                <span class="value">{{ $appointment->patient?->first_name }} {{ $appointment->patient?->last_name }}</span>
            </div>
            @if($appointment->patient?->medical_record_number)
                <div class="info-row"><span class="label">{{ __('patients.medical_record') }}:</span>
                    <span class="value">{{ $appointment->patient->medical_record_number }}</span>
                </div>
            @endif
            @if($appointment->patient?->phone)
                <div class="info-row"><span class="label">{{ __('patients.phone') }}:</span>
                    <span class="value">{{ $appointment->patient->phone }}</span>
                </div>
            @endif
            @if($appointment->patient?->email)
                <div class="info-row"><span class="label">{{ __('patients.email') }}:</span>
                    <span class="value">{{ $appointment->patient->email }}</span>
                </div>
            @endif
        </div>
        <div>
            <div class="section-title">{{ __('appointments.doctor') }}</div>
            <div class="info-row"><span class="label">{{ __('general.name') }}:</span>
                <span class="value">{{ $appointment->doctor?->name ?? '—' }}</span>
            </div>
            @if($appointment->room)
                <div class="info-row"><span class="label">{{ __('appointments.room') }}:</span>
                    <span class="value">{{ $appointment->room }}</span>
                </div>
            @endif
            <div class="info-row"><span class="label">{{ __('appointments.status') }}:</span>
                <span class="value">{{ __('appointments.status_'.$appointment->status) }}</span>
            </div>
            <div class="info-row"><span class="label">{{ __('appointments.type') }}:</span>
                <span class="value">{{ $appointment->appointment_type ? __('appointments.'.$appointment->appointment_type) : '—' }}</span>
            </div>
        </div>
    </div>

    @if($appointment->reason)
        <div class="section-title">{{ __('appointments.reason') }}</div>
        <div class="card">{{ $appointment->reason }}</div>
    @endif

    @if($appointment->symptoms)
        <div class="section-title">{{ __('appointments.symptoms') }}</div>
        <div class="card">{{ $appointment->symptoms }}</div>
    @endif

    @if($appointment->notes)
        <div class="section-title">{{ __('appointments.notes') }}</div>
        <div class="card">{{ $appointment->notes }}</div>
    @endif

    <div style="margin-top: 24px; padding-top: 12px; border-top: 1px dashed #d1d5db;">
        <p class="small muted">
            {{ __('appointments.voucher_note') }}
        </p>
    </div>
@endsection
