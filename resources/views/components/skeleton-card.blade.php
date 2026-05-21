@props([
    'count' => 6,
    'lines' => 3,
])

<div {{ $attributes->merge(['class' => 'animate-pulse grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4']) }}>
    @for($i = 0; $i < $count; $i++)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 space-y-3">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3.5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>
            @for($l = 0; $l < $lines - 1; $l++)
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-{{ $l % 2 === 0 ? 'full' : '4/5' }}"></div>
            @endfor
            <div class="flex justify-between items-center pt-1">
                <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded-full w-16"></div>
                <div class="h-7 bg-gray-200 dark:bg-gray-700 rounded-lg w-20"></div>
            </div>
        </div>
    @endfor
</div>
