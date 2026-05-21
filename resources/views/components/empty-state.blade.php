@props([
    'title',
    'description'  => null,
    'icon'         => 'document',   // document | users | calendar | invoice | prescription | staff | chart | files | tag | catalog
    'ctaText'      => null,
    'ctaRoute'     => null,
    'ctaPermission'=> null,
    'bullets'      => [],           // array of strings
    'compact'      => false,        // smaller version for cards / modals
])

@php
    $icons = [
        'document'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'users'        => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'calendar'     => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'invoice'      => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        'prescription' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'staff'        => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'chart'        => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'files'        => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        'tag'          => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        'catalog'      => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    ];
    $path = $icons[$icon] ?? $icons['document'];
    $py   = $compact ? 'py-10' : 'py-16';
@endphp

<div class="text-center {{ $py }} px-6">

    {{-- Illustrated icon circle --}}
    <div class="mx-auto mb-5 flex items-center justify-center
        {{ $compact ? 'w-14 h-14' : 'w-20 h-20' }}
        rounded-full bg-indigo-50 dark:bg-indigo-900/30">
        <svg class="{{ $compact ? 'w-7 h-7' : 'w-10 h-10' }} text-indigo-400 dark:text-indigo-300"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/>
        </svg>
    </div>

    {{-- Title --}}
    <h3 class="{{ $compact ? 'text-sm' : 'text-base' }} font-semibold text-gray-900 dark:text-white">
        {{ $title }}
    </h3>

    {{-- Description --}}
    @if($description)
        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            {{ $description }}
        </p>
    @endif

    {{-- Slot for extra description --}}
    @if($slot->isNotEmpty())
        <div class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            {{ $slot }}
        </div>
    @endif

    {{-- Feature bullets --}}
    @if(count($bullets) > 0)
        <ul class="mt-4 inline-flex flex-col items-start gap-1.5 text-left">
            @foreach($bullets as $bullet)
                <li class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $bullet }}
                </li>
            @endforeach
        </ul>
    @endif

    {{-- CTA Button --}}
    @if($ctaText && $ctaRoute)
        <div class="mt-6">
            @if($ctaPermission)
                @can($ctaPermission)
                    <a href="{{ $ctaRoute }}" wire:navigate
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ $ctaText }}
                    </a>
                @endcan
            @else
                <a href="{{ $ctaRoute }}" wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ $ctaText }}
                </a>
            @endif
        </div>
    @endif

</div>
