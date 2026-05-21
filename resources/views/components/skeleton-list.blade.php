@props([
    'count' => 5,
])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-2']) }}>
    @for($i = 0; $i < $count; $i++)
        <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
            <div class="flex-1 space-y-1.5">
                <div class="h-3.5 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
            <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded-full w-14"></div>
        </div>
    @endfor
</div>
