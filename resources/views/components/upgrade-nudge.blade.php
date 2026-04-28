@props([
    'type' => 'inline',  // inline | button | banner
    'clinicSlug' => null,
    'message' => null,
])

@php
    $clinic = app('current_clinic') ?? auth()->user()->clinic;
    $slug = $clinicSlug ?? $clinic?->slug ?? 'demo';
    $isOwner = auth()->user()->hasRole('owner');
    $billingRoute = route('app.billing.index', $slug);
    // ADR-008: distinguir si el bloqueo es por estado de cuenta (read-only/billing-only)
    // o por tope de uso del plan actual (cortesía/free al límite).
    $isAccessBlocked = $clinic && ! $clinic->canWrite();
    $tooltipKey = $isAccessBlocked
        ? ($isOwner ? 'general.tooltip_upgrade_account' : 'general.tooltip_account_inactive')
        : ($isOwner ? 'general.tooltip_limit_reached' : 'general.limit_reached_contact_admin');
@endphp

@if($type === 'button')
    {{-- Replaces a create button when at limit OR when access is read-only --}}
    <div class="inline-flex items-center gap-2">
        @if($isOwner)
            <a href="{{ $billingRoute }}" wire:navigate
               title="{{ __($tooltipKey) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                {{ $isAccessBlocked ? __('general.upgrade_to_unlock') : __('general.upgrade_to_continue') }}
            </a>
        @else
            <span title="{{ __($tooltipKey) }}"
                  class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs text-gray-500 dark:text-gray-400 uppercase tracking-widest cursor-not-allowed">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                {{ $isAccessBlocked ? __('general.account_inactive_short') : __('general.limit_reached_contact_admin') }}
            </span>
        @endif
    </div>
@elseif($type === 'inline')
    {{-- Small inline hint near usage bars or lists --}}
    <div class="flex items-center gap-1.5 text-xs">
        @if($isOwner)
            <a href="{{ $billingRoute }}" wire:navigate
               class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                {{ $message ?? __('general.upgrade_plan') }}
            </a>
        @else
            <span class="text-gray-500 dark:text-gray-400">
                {{ __('general.limit_reached_contact_admin') }}
            </span>
        @endif
    </div>
@elseif($type === 'banner')
    {{-- Subtle top banner for limit warnings --}}
    <div class="rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-3">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            <p class="text-sm text-indigo-700 dark:text-indigo-300 flex-1">
                {{ $message ?? __('general.limit_reached_description') }}
            </p>
            @if($isOwner)
                <a href="{{ $billingRoute }}" wire:navigate
                   class="flex-shrink-0 inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-md transition">
                    {{ __('general.view_plans') }}
                </a>
            @endif
        </div>
    </div>
@endif
