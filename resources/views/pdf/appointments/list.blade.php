@extends('pdf._layout', [
    'title' => __('appointments.title'),
    'subheader' => $filtersText ?: '',
])

@section('content')
    <div class="meta">
        <div class="meta-item"><strong>{{ __('general.total') }}:</strong> {{ $appointments->count() }}</div>
        @if(!empty($filtersText))
            <div class="meta-item"><strong>{{ __('reports.filters') }}:</strong> {{ $filtersText }}</div>
        @endif
    </div>

    @if($appointments->isEmpty())
        <p class="muted">{{ __('appointments.no_appointments') }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 12%;">{{ __('appointments.date') }}</th>
                    <th style="width: 10%;">{{ __('appointments.time') }}</th>
                    <th style="width: 22%;">{{ __('appointments.patient') }}</th>
                    <th style="width: 18%;">{{ __('appointments.doctor') }}</th>
                    <th style="width: 14%;">{{ __('appointments.type') }}</th>
                    <th style="width: 10%;">{{ __('appointments.status') }}</th>
                    <th style="width: 10%;">{{ __('appointments.room') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $i => $a)
                    <tr @class(['zebra' => $i % 2 === 1])>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a->appointment_date?->format('d/m/Y') }}</td>
                        <td>
                            {{ $a->start_time?->format('H:i') }}
                            @if($a->end_time) – {{ $a->end_time?->format('H:i') }} @endif
                        </td>
                        <td>
                            {{ $a->patient?->first_name }} {{ $a->patient?->last_name }}
                            @if($a->patient?->phone)
                                <div class="small muted">{{ $a->patient->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $a->doctor?->name ?? '—' }}</td>
                        <td>{{ $a->appointment_type ? __('appointments.'.$a->appointment_type) : '—' }}</td>
                        <td>
                            @php
                                $statusClass = match($a->status) {
                                    'completed' => 'badge-green',
                                    'cancelled', 'no_show' => 'badge-red',
                                    'in_progress', 'waiting' => 'badge-amber',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ __('appointments.status_'.$a->status) }}</span>
                        </td>
                        <td>{{ $a->room ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
