@extends('pdf._layout', [
    'title' => __('records.medical_records'),
    'subheader' => $patient->first_name.' '.$patient->last_name,
])

@push('styles')
<style>
    .patient-banner {
        background: #4f46e5; color: #fff; padding: 10px 14px;
        border-radius: 4px; margin-bottom: 10px;
    }
    .patient-banner .name { font-size: 14px; font-weight: 700; }
    .patient-banner .meta { font-size: 9px; opacity: 0.9; margin-top: 2px; }

    .record-meta { background: #f9fafb; border: 1px solid #e5e7eb; padding: 8px 10px; border-radius: 4px; margin-bottom: 10px; }
    .record-meta .row { display: table; width: 100%; }
    .record-meta .col { display: table-cell; vertical-align: top; padding-right: 8px; font-size: 9px; }

    .vitals { display: table; width: 100%; margin-bottom: 8px; }
    .vital { display: table-cell; padding: 6px 8px; border: 1px solid #e5e7eb; text-align: center; }
    .vital .v { font-size: 13px; font-weight: 700; color: #111827; }
    .vital .l { font-size: 8px; color: #6b7280; text-transform: uppercase; }

    .signature-row { margin-top: 30px; display: table; width: 100%; }
    .signature { display: table-cell; width: 50%; padding: 0 12px; text-align: center; font-size: 9px; color: #6b7280; }
    .signature .line { border-top: 1px solid #6b7280; margin-bottom: 4px; height: 1px; }

    .pre-text { white-space: pre-wrap; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
</style>
@endpush

@section('content')
    <div class="patient-banner">
        <div class="name">{{ $patient->first_name }} {{ $patient->last_name }}</div>
        <div class="meta">
            @if($patient->medical_record_number) {{ __('patients.medical_record') }}: {{ $patient->medical_record_number }} @endif
            @if($patient->birth_date) · {{ \Carbon\Carbon::parse($patient->birth_date)->age }} {{ __('general.years') }} @endif
            @if($patient->gender) · {{ __('patients.'.$patient->gender) }} @endif
            @if($patient->blood_type) · {{ $patient->blood_type }} @endif
        </div>
    </div>

    <div class="record-meta">
        <div class="row">
            <div class="col"><strong>{{ __('records.created_at') }}:</strong>
                {{ optional($record->created_at)->format('d/m/Y H:i') }}
            </div>
            <div class="col"><strong>{{ __('records.created_by') }}:</strong>
                {{ $record->doctor?->name ?? '—' }}
            </div>
            <div class="col"><strong>{{ __('records.field_status') }}:</strong>
                {{ __('records.status_'.$record->status) }}
            </div>
            <div class="col"><strong>{{ __('records.field_record_type') }}:</strong>
                {{ __('records.type_'.$record->record_type) }}
            </div>
        </div>
        @if($record->title)
            <div style="margin-top:6px;font-size:11px;font-weight:700;">{{ $record->title }}</div>
        @endif
    </div>

    {{-- SOAP --}}
    @if($record->chief_complaint)
        <div class="section-title">{{ __('records.field_chief_complaint') }}</div>
        <div class="card pre-text">{{ $record->chief_complaint }}</div>
    @endif

    @if($record->present_illness)
        <div class="section-title">{{ __('records.field_present_illness') }}</div>
        <div class="card pre-text">{{ $record->present_illness }}</div>
    @endif

    @if(is_array($record->vital_signs) && count(array_filter($record->vital_signs)))
        <div class="section-title">{{ __('records.form_section_vital_signs') }}</div>
        <div class="vitals">
            @foreach([
                'temperature' => __('records.field_temperature'),
                'heart_rate' => __('records.field_heart_rate'),
                'blood_pressure' => __('records.field_blood_pressure'),
                'respiratory_rate' => __('records.field_respiratory_rate'),
                'oxygen_saturation' => __('records.field_oxygen_saturation'),
                'weight' => __('records.field_weight'),
                'height' => __('records.field_height'),
            ] as $key => $label)
                @if(!empty($record->vital_signs[$key]))
                    <div class="vital">
                        <div class="v">{{ $record->vital_signs[$key] }}</div>
                        <div class="l">{{ $label }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if($record->physical_examination)
        <div class="section-title">{{ __('records.field_physical_examination') }}</div>
        <div class="card pre-text">{{ $record->physical_examination }}</div>
    @endif

    @if($record->assessment)
        <div class="section-title">{{ __('records.field_assessment') }}</div>
        <div class="card pre-text">{{ $record->assessment }}</div>
    @endif

    @if(is_array($record->diagnoses) && count($record->diagnoses))
        <div class="section-title">{{ __('records.field_diagnoses') }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">{{ __('records.field_diagnosis_code') }}</th>
                    <th>{{ __('records.field_diagnosis_description') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->diagnoses as $i => $dx)
                    @if(!empty($dx['code']) || !empty($dx['description']))
                        <tr @class(['zebra' => $i % 2 === 1])>
                            <td>{{ $dx['code'] ?? '—' }}</td>
                            <td>{{ $dx['description'] ?? '—' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    @if($record->plan)
        <div class="section-title">{{ __('records.field_plan') }}</div>
        <div class="card pre-text">{{ $record->plan }}</div>
    @endif

    @if(is_array($record->prescriptions) && count($record->prescriptions))
        <div class="section-title">{{ __('records.field_prescriptions') }}</div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('records.field_prescription_drug') }}</th>
                    <th>{{ __('records.field_prescription_dosage') }}</th>
                    <th>{{ __('records.field_prescription_duration') }}</th>
                    <th>{{ __('records.field_prescription_notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->prescriptions as $i => $rx)
                    @if(!empty($rx['drug']))
                        <tr @class(['zebra' => $i % 2 === 1])>
                            <td>{{ $rx['drug'] }}</td>
                            <td>{{ $rx['dosage'] ?? '—' }}</td>
                            <td>{{ $rx['duration'] ?? '—' }}</td>
                            <td>{{ $rx['notes'] ?? '' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    @if($record->content)
        <div class="section-title">{{ __('records.field_title') }}</div>
        <div class="card pre-text">{{ $record->content }}</div>
    @endif

    <div class="signature-row">
        <div class="signature">
            <div class="line"></div>
            {{ $record->doctor?->name ?? '' }}
            <div>{{ __('appointments.doctor') }}</div>
        </div>
        <div class="signature">
            <div class="line"></div>
            {{ $patient->first_name }} {{ $patient->last_name }}
            <div>{{ __('appointments.patient') }}</div>
        </div>
    </div>
@endsection
