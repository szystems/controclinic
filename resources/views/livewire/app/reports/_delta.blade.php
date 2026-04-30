{{-- Delta badge: shows % change vs previous period.
     $delta is null when previous = 0, an int/float otherwise.
     $invert = true means a decrease is "good" (e.g. cancellations, no_show). --}}
@php($invert = $invert ?? false)
@if($delta === null)
    <span class="inline-flex items-center text-[11px] text-gray-400 dark:text-gray-500">—</span>
@elseif($delta == 0)
    <span class="inline-flex items-center text-[11px] text-gray-500 dark:text-gray-400">0%</span>
@else
    @php($isUp = $delta > 0)
    @php($isGood = $invert ? !$isUp : $isUp)
    <span class="inline-flex items-center gap-0.5 text-[11px] font-medium {{ $isGood ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
        @if($isUp)
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
        @else
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
        @endif
        {{ abs($delta) }}%
    </span>
@endif
