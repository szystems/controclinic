@extends('pdf._layout', ['title' => __('patients.list_title'), 'subheader' => __('general.total').': '.$patients->count()])

@section('content')
    @if(!empty($filtersText))
    <div class="meta">
        <span class="meta-item"><strong>{{ __('reports.filters') }}:</strong> {{ $filtersText }}</span>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:6%">#</th>
                <th style="width:24%">{{ __('patients.full_name') }}</th>
                <th style="width:14%">{{ __('patients.medical_record_number') }}</th>
                <th style="width:18%">{{ __('patients.email') }}</th>
                <th style="width:12%">{{ __('patients.phone') }}</th>
                <th style="width:8%" class="text-center">{{ __('patients.age') }}</th>
                <th style="width:10%">{{ __('patients.gender') }}</th>
                <th style="width:8%" class="text-center">{{ __('general.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patients as $i => $p)
            <tr @class(['zebra' => $i % 2])>
                <td class="muted">{{ $i + 1 }}</td>
                <td><strong>{{ trim($p->first_name.' '.$p->last_name) }}</strong></td>
                <td class="small muted">{{ $p->medical_record_number ?? '—' }}</td>
                <td class="small">{{ $p->email ?? '—' }}</td>
                <td class="small">{{ $p->phone ?? '—' }}</td>
                <td class="text-center">{{ $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->age : '—' }}</td>
                <td class="small">{{ $p->gender ? __('patients.'.$p->gender) : '—' }}</td>
                <td class="text-center">
                    @if($p->is_active)
                        <span class="badge badge-green">{{ __('general.active') }}</span>
                    @else
                        <span class="badge badge-gray">{{ __('general.inactive') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($patients->isEmpty())
        <p class="muted text-center" style="margin-top:20px;">{{ __('patients.no_patients') }}</p>
    @endif
@endsection
