@extends('pdf._layout', [
    'title' => __('staff.title'),
    'subheader' => __('staff.subtitle'),
])

@push('styles')
<style>
    table.staff { width: 100%; border-collapse: collapse; font-size: 9.5px; }
    table.staff th, table.staff td { border: 1px solid #e5e7eb; padding: 5px 6px; vertical-align: top; }
    table.staff thead th { background: #eef2ff; color: #312e81; text-align: left; font-weight: 600; }
    table.staff tr:nth-child(even) td { background: #fafafa; }
    .role-badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 8.5px; font-weight: 600; }
    .role-owner { background: #fce7f3; color: #9d174d; }
    .role-doctor { background: #dbeafe; color: #1e40af; }
    .role-assistant { background: #d1fae5; color: #065f46; }
    .role-secretary { background: #fef3c7; color: #92400e; }
    .role-receptionist { background: #ede9fe; color: #5b21b6; }
    .role-admin { background: #f3f4f6; color: #1f2937; }
    .status-active { color: #047857; font-weight: 600; }
    .status-inactive { color: #b91c1c; font-weight: 600; }
    .filters-bar { background: #f9fafb; border: 1px solid #e5e7eb; padding: 6px 8px; border-radius: 4px; font-size: 9px; color: #4b5563; margin-bottom: 8px; }
</style>
@endpush

@section('content')
    @php
        $activeFilters = collect([
            $filters['search'] ?? null ? __('general.search').': '.$filters['search'] : null,
            !empty($filters['role']) ? __('staff.role').': '.$filters['role'] : null,
            !empty($filters['status']) ? __('general.status').': '.__('general.'.$filters['status']) : null,
        ])->filter()->implode(' · ');
    @endphp

    <div class="filters-bar">
        <strong>{{ __('general.total') }}:</strong> {{ $members->count() }}
        @if($activeFilters)
            &nbsp;|&nbsp; <strong>{{ __('general.search') }}:</strong> {{ $activeFilters }}
        @endif
        &nbsp;|&nbsp; {{ now()->format('Y-m-d H:i') }}
    </div>

    <table class="staff">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 26%;">{{ __('general.name') }}</th>
                <th style="width: 28%;">{{ __('staff.email') }}</th>
                <th style="width: 16%;">{{ __('staff.role') }}</th>
                <th style="width: 14%;">{{ __('staff.phone') }}</th>
                <th style="width: 12%;">{{ __('general.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $m->name }}</strong></td>
                    <td>{{ $m->email }}</td>
                    <td><span class="role-badge role-{{ $m->role }}">{{ $m->role_label }}</span></td>
                    <td>{{ $m->phone ?? '—' }}</td>
                    <td>
                        @if($m->is_active)
                            <span class="status-active">{{ __('general.active') }}</span>
                        @else
                            <span class="status-inactive">{{ __('general.inactive') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; padding: 12px; color:#6b7280;">{{ __('staff.no_members') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
