@props([
    'rows'    => 5,
    'cols'    => 4,
    'header'  => true,
])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    @if($header)
    <div class="flex items-center justify-between mb-4 gap-3">
        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-lg w-48"></div>
        <div class="flex gap-2">
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-lg w-24"></div>
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-lg w-24"></div>
        </div>
    </div>
    @endif

    {{-- Filter bar --}}
    <div class="flex gap-3 mb-4">
        <div class="h-9 bg-gray-200 dark:bg-gray-700 rounded-lg flex-1 max-w-xs"></div>
        <div class="h-9 bg-gray-200 dark:bg-gray-700 rounded-lg w-28"></div>
        <div class="h-9 bg-gray-200 dark:bg-gray-700 rounded-lg w-28"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        {{-- Table header --}}
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-3 grid gap-4"
             style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr))">
            @for($i = 0; $i < $cols; $i++)
                <div class="h-3.5 bg-gray-200 dark:bg-gray-700 rounded w-{{ $i === 0 ? '3/4' : '1/2' }}"></div>
            @endfor
        </div>

        {{-- Rows --}}
        @for($r = 0; $r < $rows; $r++)
            <div class="px-6 py-4 grid gap-4 border-b border-gray-100 dark:border-gray-700/50 last:border-0"
                 style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr))">
                @for($c = 0; $c < $cols; $c++)
                    @if($c === 0)
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
                            <div class="h-3.5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                        </div>
                    @else
                        <div class="h-3.5 bg-gray-{{ $r % 2 === 0 ? '200' : '150' }} dark:bg-gray-700 rounded w-{{ $c % 2 === 0 ? '2/3' : '1/2' }}"></div>
                    @endif
                @endfor
            </div>
        @endfor
    </div>

    {{-- Pagination placeholder --}}
    <div class="flex items-center justify-between mt-4">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
        <div class="flex gap-1">
            @for($i = 0; $i < 5; $i++)
                <div class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
            @endfor
        </div>
    </div>
</div>
