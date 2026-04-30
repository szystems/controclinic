@extends('pdf._layout', ['title' => __('patients.patient_card'), 'subheader' => $patient->medical_record_number ? '#'.$patient->medical_record_number : ''])

@section('content')
    {{-- Patient header --}}
    <div class="card" style="background:#eef2ff;border-color:#c7d2fe;">
        <div style="display:table;width:100%;">
            <div style="display:table-cell;vertical-align:middle;">
                <div style="font-size:16px;font-weight:700;color:#111827;">
                    {{ trim($patient->first_name.' '.$patient->last_name) }}
                </div>
                <div class="small muted" style="margin-top:2px;">
                    @if($patient->birth_date)
                        {{ __('patients.born') }}: {{ \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') }}
                        ({{ \Carbon\Carbon::parse($patient->birth_date)->age }} {{ __('general.years') }})
                    @endif
                    @if($patient->gender) · {{ __('patients.'.$patient->gender) }} @endif
                </div>
            </div>
            <div style="display:table-cell;text-align:right;vertical-align:middle;">
                @if($patient->is_active)
                    <span class="badge badge-green">{{ __('general.active') }}</span>
                @else
                    <span class="badge badge-gray">{{ __('general.inactive') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Contact info --}}
    <div class="section-title">{{ __('patients.contact_info') }}</div>
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="label">{{ __('patients.email') }}</div>
                <div class="value">{{ $patient->email ?? '—' }}</div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="label">{{ __('patients.phone') }}</div>
                <div class="value">{{ $patient->phone ?? '—' }}</div>
            </div>
        </div>
    </div>
    @if($patient->address)
    <div class="card">
        <div class="label">{{ __('patients.address') }}</div>
        <div class="value">{{ $patient->address }}</div>
    </div>
    @endif

    {{-- Medical info --}}
    @if($patient->blood_type || $patient->allergies || $patient->chronic_conditions)
    <div class="section-title">{{ __('patients.medical_info') }}</div>
    <div class="grid-3">
        <div>
            <div class="card">
                <div class="label">{{ __('patients.blood_type') }}</div>
                <div class="value">{{ $patient->blood_type ?? '—' }}</div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="label">{{ __('patients.allergies') }}</div>
                <div class="value small">{{ $patient->allergies ?? '—' }}</div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="label">{{ __('patients.chronic_conditions') }}</div>
                <div class="value small">{{ $patient->chronic_conditions ?? '—' }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Emergency contact --}}
    @php $emergency = is_array($patient->emergency_contacts ?? null) ? ($patient->emergency_contacts[0] ?? null) : null; @endphp
    @if($emergency && (($emergency['name'] ?? null) || ($emergency['phone'] ?? null)))
    <div class="section-title">{{ __('patients.emergency_contact') }}</div>
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="label">{{ __('patients.name') }}</div>
                <div class="value">{{ $emergency['name'] ?? '—' }}</div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="label">{{ __('patients.phone') }}</div>
                <div class="value">{{ $emergency['phone'] ?? '—' }}@if(!empty($emergency['relationship'])) <span class="small muted">· {{ $emergency['relationship'] }}</span>@endif</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent appointments --}}
    @if($appointments->count() > 0)
    <div class="section-title">{{ __('patients.recent_appointments') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width:14%">{{ __('reports.col_date') }}</th>
                <th style="width:10%">{{ __('reports.col_time') }}</th>
                <th style="width:24%">{{ __('reports.col_doctor') }}</th>
                <th style="width:22%">{{ __('reports.col_reason') }}</th>
                <th style="width:14%">{{ __('reports.col_type') }}</th>
                <th style="width:14%">{{ __('reports.col_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $a)
            <tr>
                <td>{{ \Carbon\Carbon::parse($a->appointment_date)->format('d/m/Y') }}</td>
                <td>{{ $a->start_time }}</td>
                <td>{{ $a->doctor->name ?? '—' }}</td>
                <td class="small">{{ $a->reason ?? '—' }}</td>
                <td class="small muted">{{ __('reports.type_'.str_replace('_', '', $a->appointment_type)) }}</td>
                <td>
                    @php
                        $cls = match($a->status) {
                            'completed' => 'badge-green',
                            'cancelled', 'no_show' => 'badge-red',
                            'in_progress', 'waiting' => 'badge-amber',
                            default => 'badge',
                        };
                    @endphp
                    <span class="badge {{ $cls }}">{{ __('reports.status_'.str_replace('_', '', $a->status)) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Notes --}}
    @if($patient->notes)
    <div class="section-title">{{ __('patients.notes') }}</div>
    <div class="card">
        <div class="small">{{ $patient->notes }}</div>
    </div>
    @endif
@endsection
