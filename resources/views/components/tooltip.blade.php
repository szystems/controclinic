@props([
    'text',             // tooltip text
    'position' => 'top', // top | bottom | left | right
])
@php
    $posClasses = match($position) {
        'bottom' => 'top-full mt-1.5 left-1/2 -translate-x-1/2',
        'left'   => 'right-full mr-1.5 top-1/2 -translate-y-1/2',
        'right'  => 'left-full ml-1.5 top-1/2 -translate-y-1/2',
        default  => 'bottom-full mb-1.5 left-1/2 -translate-x-1/2',
    };
    $arrowClasses = match($position) {
        'bottom' => 'top-0 -translate-y-full left-1/2 -translate-x-1/2 border-b-gray-700 dark:border-b-gray-600',
        'left'   => 'right-0 translate-x-full top-1/2 -translate-y-1/2 border-l-gray-700 dark:border-l-gray-600',
        'right'  => 'left-0 -translate-x-full top-1/2 -translate-y-1/2 border-r-gray-700 dark:border-r-gray-600',
        default  => 'bottom-0 translate-y-full left-1/2 -translate-x-1/2 border-t-gray-700 dark:border-t-gray-600',
    };
@endphp

<span
    x-data="{ open: false }"
    @mouseenter="open = true"
    @mouseleave="open = false"
    @focus="open = true"
    @blur="open = false"
    class="relative inline-flex items-center"
    {{ $attributes }}
>
    {{-- Trigger slot or default question-mark icon --}}
    @if($slot->isEmpty())
        <button
            type="button"
            tabindex="0"
            class="inline-flex items-center justify-center w-4 h-4 rounded-full text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
            aria-label="{{ $text }}"
        >
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
        </button>
    @else
        {{ $slot }}
    @endif

    {{-- Tooltip bubble --}}
    <span
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $posClasses }} w-56 rounded-lg bg-gray-700 dark:bg-gray-600 px-3 py-2 text-xs text-white shadow-lg pointer-events-none"
        role="tooltip"
    >
        {{ $text }}
        {{-- Arrow --}}
        <span class="absolute w-0 h-0 border-4 border-transparent {{ $arrowClasses }}"></span>
    </span>
</span>
