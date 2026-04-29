<div class="mt-10">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('profile.activity_log') }}</h2>
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
                @forelse ($activities as $activity)
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $activity->description }}</td>
                        <td class="px-4 py-2 whitespace-pre-line text-sm text-gray-900 dark:text-gray-100">
                            @if($activity->properties && $activity->properties->has('attributes'))
                                @foreach($activity->properties['attributes'] as $key => $value)
                                    <div><span class="font-semibold">{{ $key }}:</span> {{ $value }}</div>
                                @endforeach
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
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
</div>
