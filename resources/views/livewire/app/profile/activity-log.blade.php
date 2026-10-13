@php
    $activityRows = (new \App\Support\ProfileActivityPresenter($currentClinic))->present($activities);
@endphp

<div class="mt-10 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" x-data="{ open: {{ request()->integer('page') > 1 ? 'true' : 'false' }} }">
    <button type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('profile.activity_log') }}</h2>
        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak class="border-t border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('profile.activity_date') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('profile.activity_event') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('profile.activity_changes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activityRows as $row)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 align-top">{{ $row['when'] }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 align-top">{{ $row['summary'] }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 align-top">
                                @forelse ($row['changes'] as $change)
                                    <div>
                                        <span class="font-medium">{{ $change['label'] }}:</span>
                                        @if (isset($change['from']))
                                            <span class="text-gray-400 line-through">{{ $change['from'] }}</span>
                                            <span aria-hidden="true">→</span>
                                            <span>{{ $change['to'] }}</span>
                                        @else
                                            {{ $change['value'] }}
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-gray-400">—</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">{{ __('profile.no_activity') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activities instanceof \Illuminate\Contracts\Pagination\Paginator && $activities->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
