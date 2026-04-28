@props([
    'plans', // Collection of Plan models
])

@php
    $comparisonRows = [
        'row_users',
        'row_patients',
        'row_appointments',
        'row_email_reminders',
        'row_sms_reminders',
        'row_whatsapp_reminders',
        'row_booking',
        'row_basic_reports',
        'row_advanced_reports',
        'row_custom_branding',
        'row_api',
        'row_white_label',
        'row_email_support',
        'row_priority_support',
        'row_24_7_support',
    ];

    $popularIndex = null;
    foreach ($plans as $i => $plan) {
        if ($plan->is_popular) {
            $popularIndex = $i;
            break;
        }
    }
@endphp

<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200">
                <th class="py-4 px-4 text-left text-sm font-semibold text-gray-900">{{ __('features.feature') }}</th>
                @foreach ($plans as $i => $plan)
                    <th class="py-4 px-4 text-center text-sm font-semibold {{ $plan->is_popular ? 'text-indigo-600 bg-indigo-50 ' . ($loop->first ? 'rounded-tl-lg' : '') . ($loop->last ? ' rounded-tr-lg' : '') : 'text-gray-900' }}">
                        {{ $plan->name }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($comparisonRows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-4 text-sm text-gray-600">{{ __("features.{$row}") }}</td>
                    @foreach ($plans as $plan)
                        @php $value = $plan->getComparisonValue($row); @endphp
                        <td class="py-4 px-4 text-center {{ $plan->is_popular ? 'bg-indigo-50' : '' }}">
                            @if (is_bool($value))
                                @if ($value)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            @else
                                <span class="text-sm {{ $plan->is_popular ? 'text-indigo-600 font-medium' : 'text-gray-900' }}">{{ $value }}</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
