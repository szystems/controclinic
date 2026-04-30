@extends('pdf._layout', [
    'title' => __('records.prescription_pdf_title'),
    'subheader' => __('records.prescription_pdf_subtitle'),
])

@push('styles')
<style>
    .rx-symbol {
        font-size: 46px;
        font-weight: 700;
        color: #4f46e5;
        line-height: 1;
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
        letter-spacing: -1px;
    }
    .doctor-block {
        background: #eef2ff;
        border-left: 4px solid #4f46e5;
        padding: 10px 12px;
        border-radius: 0 4px 4px 0;
        margin-bottom: 14px;
    }
    .doctor-name { font-size: 14px; font-weight: 700; color: #111827; }
    .doctor-meta { font-size: 9.5px; color: #4b5563; margin-top: 2px; }
    .patient-row {
        display: table; width: 100%;
        background: #fafafa; border: 1px solid #e5e7eb;
        padding: 8px 10px; margin-bottom: 14px; border-radius: 4px;
    }
    .patient-row > div { display: table-cell; vertical-align: top; padding-right: 10px; }
    .patient-row .lbl { font-size: 8px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.4px; }
    .patient-row .val { font-size: 11px; font-weight: 600; color: #111827; margin-top: 1px; }

    .rx-list { margin: 6px 0 12px; }
    .rx-item {
        border: 1px solid #e5e7eb;
        border-left: 3px solid #6366f1;
        padding: 10px 12px;
        margin-bottom: 8px;
        border-radius: 0 4px 4px 0;
        page-break-inside: avoid;
    }
    .rx-item .num {
        display: inline-block;
        width: 22px; height: 22px; line-height: 22px;
        text-align: center;
        background: #4f46e5; color: white;
        font-weight: 700; border-radius: 50%;
        font-size: 10px;
        margin-right: 6px;
        vertical-align: middle;
    }
    .rx-item .drug { font-size: 13px; font-weight: 700; color: #111827; display: inline; }
    .rx-item .row { margin-top: 6px; font-size: 10px; color: #374151; }
    .rx-item .row strong { color: #111827; }
    .rx-item .notes {
        margin-top: 6px;
        padding: 6px 8px;
        background: #fffbeb;
        border-left: 2px solid #f59e0b;
        font-size: 9.5px;
        color: #78350f;
    }
    .signature-block {
        margin-top: 36px;
        text-align: center;
    }
    .signature-line {
        width: 60%;
        margin: 0 auto 4px;
        border-top: 1px solid #6b7280;
    }
    .signature-name { font-size: 11px; font-weight: 700; color: #111827; }
    .signature-meta { font-size: 9px; color: #6b7280; margin-top: 1px; }

    .footer-note {
        margin-top: 20px;
        font-size: 8.5px;
        color: #6b7280;
        text-align: center;
        padding: 6px;
        border-top: 1px dashed #d1d5db;
    }
    .date-issue {
        text-align: right;
        font-size: 9.5px;
        color: #4b5563;
        margin-bottom: 6px;
    }
    .patient-title {
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 5px;
    }
</style>
@endpush

@section('content')
    @php
        $doctor = $record->doctor;
        $specialties = is_array($doctor?->specialties) ? implode(', ', $doctor->specialties) : null;
        $age = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age : null;
    @endphp

    <div class="date-issue">
        {{ __('records.prescription_issued_at') }}: <strong>{{ optional($record->created_at)->isoFormat('LL') }}</strong>
    </div>

    {{-- Doctor block --}}
    <div class="doctor-block">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; vertical-align: middle;">
                <div class="doctor-name">{{ $doctor?->name ?? '—' }}</div>
                @if($specialties)
                    <div class="doctor-meta">{{ $specialties }}</div>
                @endif
                @if($doctor?->license_number)
                    <div class="doctor-meta">
                        <strong>{{ __('staff.license_number') }}:</strong> {{ $doctor->license_number }}
                    </div>
                @endif
            </div>
            <div style="display: table-cell; vertical-align: middle; text-align: right; width: 80px;">
                <div class="rx-symbol">Rx</div>
            </div>
        </div>
    </div>

    {{-- Patient row --}}
    <div class="patient-title">{{ __('patients.patient_information') }}</div>
    <div class="patient-row">
        <div>
            <div class="lbl">{{ __('patients.full_name') }}</div>
            <div class="val">{{ $patient->first_name }} {{ $patient->last_name }}</div>
        </div>
        @if($age !== null)
        <div style="width: 70px;">
            <div class="lbl">{{ __('patients.age') }}</div>
            <div class="val">{{ $age }} {{ __('patients.years') }}</div>
        </div>
        @endif
        @if($patient->gender)
        <div style="width: 90px;">
            <div class="lbl">{{ __('patients.gender') }}</div>
            <div class="val">{{ __('patients.'.$patient->gender) }}</div>
        </div>
        @endif
        @if($patient->medical_record_number)
        <div style="width: 110px;">
            <div class="lbl">{{ __('patients.medical_record_number') }}</div>
            <div class="val">{{ $patient->medical_record_number }}</div>
        </div>
        @endif
    </div>

    {{-- Rx list --}}
    <div class="section-title">{{ __('records.prescription_indication') }}</div>
    <div class="rx-list">
        @foreach($record->prescriptions as $i => $rx)
            @if(!empty($rx['drug']))
                <div class="rx-item">
                    <span class="num">{{ $i + 1 }}</span>
                    <span class="drug">{{ $rx['drug'] }}</span>

                    @if(!empty($rx['dosage']) || !empty($rx['duration']))
                        <div class="row">
                            @if(!empty($rx['dosage']))
                                <strong>{{ __('records.field_prescription_dosage') }}:</strong> {{ $rx['dosage'] }}
                            @endif
                            @if(!empty($rx['duration']))
                                @if(!empty($rx['dosage'])) &nbsp;·&nbsp; @endif
                                <strong>{{ __('records.field_prescription_duration') }}:</strong> {{ $rx['duration'] }}
                            @endif
                        </div>
                    @endif

                    @if(!empty($rx['notes']))
                        <div class="notes">
                            <strong>{{ __('records.field_prescription_notes') }}:</strong> {{ $rx['notes'] }}
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>

    @if(is_array($record->diagnoses) && count(array_filter($record->diagnoses, fn($d) => !empty($d['description']) || !empty($d['code']))))
        <div class="section-title">{{ __('records.field_diagnoses') }}</div>
        <div style="font-size: 10px; color: #374151; margin-bottom: 10px;">
            @foreach($record->diagnoses as $dx)
                @if(!empty($dx['description']) || !empty($dx['code']))
                    <div style="margin-bottom: 3px;">
                        @if(!empty($dx['code']))
                            <span style="font-family: monospace; background:#f3f4f6; padding: 1px 5px; border-radius: 2px; font-size: 9px;">{{ $dx['code'] }}</span>
                        @endif
                        {{ $dx['description'] ?? '' }}
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Signature --}}
    <div class="signature-block">
        <div class="signature-line"></div>
        <div class="signature-name">{{ $doctor?->name ?? '' }}</div>
        @if($specialties)
            <div class="signature-meta">{{ $specialties }}</div>
        @endif
        @if($doctor?->license_number)
            <div class="signature-meta">{{ __('staff.license_number') }}: {{ $doctor->license_number }}</div>
        @endif
    </div>

    <div class="footer-note">
        {{ __('records.prescription_footer_note') }}
    </div>
@endsection
