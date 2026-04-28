@props([
    'plan',                    // Plan model instance
    'billingCycle' => 'monthly', // 'monthly' or 'yearly'
    'selected' => false,       // is this plan currently selected?
    'currentPlan' => null,     // slug of user's current plan (for billing page)
    'context' => 'pricing',    // 'pricing', 'billing', 'onboarding'
    'onSelect' => null,        // wire:click action for onboarding
    'onCheckout' => null,      // wire:click action for billing checkout
    'onChangePlan' => null,    // wire:click action for billing plan change
    'isSubscribed' => false,   // is user currently subscribed? (billing context)
])

@php
    $isPopular = $plan->is_popular;
    $isFree = $plan->is_free;
    $isEnterprise = $plan->is_enterprise;
    $isCurrent = $currentPlan === $plan->slug;

    $price = $billingCycle === 'yearly' ? $plan->yearly_monthly_price : $plan->monthly_price;
    $yearlyTotal = $plan->yearly_price;
    $savings = $plan->annual_savings;

    $features = $plan->display_features;
@endphp

@if ($context === 'onboarding')
    {{-- Onboarding: compact selectable card --}}
    <div wire:click="{{ $onSelect }}('{{ $plan->slug }}')"
        class="relative p-5 rounded-xl border-2 cursor-pointer transition
        {{ $selected ? 'border-primary ring-2 ring-primary/20 bg-primary/5 dark:bg-primary/10' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $plan->name }}</h3>
            @if ($selected)
                <span class="text-xs px-2 py-1 bg-primary/10 text-primary rounded-full font-medium">{{ __('onboarding.selected') }}</span>
            @elseif (!$isFree && !$isEnterprise)
                <span class="text-xs px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full font-medium">{{ __('onboarding.coming_soon') }}</span>
            @endif
        </div>
        <div class="mb-4">
            @if ($isEnterprise)
                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ __('onboarding.contact_us') }}</span>
            @elseif ($isFree)
                <span class="text-3xl font-bold text-gray-900 dark:text-white">$0</span>
                <span class="text-gray-500 dark:text-gray-400">{{ __('onboarding.forever_free') }}</span>
            @else
                <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($plan->monthly_price, 0) }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ __('onboarding.per_month') }}</span>
            @endif
        </div>
        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            @foreach (array_slice($features, 0, $isEnterprise ? 5 : ($isFree ? 4 : 5)) as $feature)
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $feature }}
                </li>
            @endforeach
        </ul>
    </div>

@elseif ($context === 'billing')
    {{-- Billing: action card with checkout/change buttons --}}
    <div class="relative bg-white dark:bg-gray-800 rounded-xl border-2 p-6 transition flex flex-col
        {{ $isPopular ? 'border-primary shadow-lg' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}
        {{ $isCurrent ? 'ring-2 ring-primary/20' : '' }}">

        @if ($isPopular)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">{{ __('billing.most_popular') }}</span>
            </div>
        @endif

        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
        </div>

        <div class="mb-6">
            @if ($isEnterprise)
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('billing.contact_us') }}</span>
            @elseif ($price)
                <span class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($price, 0) }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ __('features.per_month') }}</span>
                @if ($billingCycle === 'yearly' && $yearlyTotal)
                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                        ${{ number_format($yearlyTotal, 0) }}{{ __('features.per_year') }}
                    </p>
                @endif
            @endif
        </div>

        <ul class="space-y-2.5 mb-6 flex-1">
            @foreach ($features as $feature)
                <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $feature }}
                </li>
            @endforeach
        </ul>

        <div class="mt-auto">
        @if ($isCurrent)
            <button disabled class="w-full py-2.5 px-4 text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                {{ __('billing.current') }}
            </button>
        @elseif ($isEnterprise)
            <a href="{{ route('contact') }}?subject=enterprise"
               class="block w-full py-2.5 px-4 text-sm font-medium text-center text-white bg-gray-900 dark:bg-gray-600 hover:bg-gray-800 dark:hover:bg-gray-500 rounded-lg transition">
                {{ __('billing.contact_sales') }}
            </a>
        @elseif ($isSubscribed && $onChangePlan)
            <button wire:click="{{ $onChangePlan }}('{{ $plan->slug }}')" wire:confirm="{{ __('billing.confirm_change', ['plan' => $plan->name]) }}"
                class="w-full py-2.5 px-4 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition">
                {{ __('billing.switch_to', ['plan' => $plan->name]) }}
            </button>
        @elseif ($onCheckout)
            <button wire:click="{{ $onCheckout }}('{{ $plan->slug }}')"
                class="w-full py-2.5 px-4 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition">
                {{ __('billing.upgrade_to', ['plan' => $plan->name]) }}
            </button>
        @endif
        </div>
    </div>

@else
    {{-- Pricing: public marketing card --}}
    <div class="{{ $isPopular ? 'bg-indigo-600 rounded-2xl p-8 text-left relative shadow-xl lg:scale-105' : 'bg-white rounded-2xl border border-gray-200 p-8 text-left hover:shadow-lg transition-shadow' }}">
        @if ($isPopular)
            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                <span class="bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 text-xs font-bold px-4 py-1 rounded-full">
                    {{ __('billing.most_popular') }}
                </span>
            </div>
        @endif

        <div class="mb-6">
            <h3 class="text-lg font-semibold {{ $isPopular ? 'text-white' : 'text-gray-900' }}">{{ $plan->name }}</h3>
            <p class="text-sm {{ $isPopular ? 'text-indigo-200' : 'text-gray-500' }} mt-1">{{ $plan->translated_description }}</p>
        </div>

        <div class="mb-6">
            @if ($isEnterprise)
                <span class="text-3xl font-bold {{ $isPopular ? 'text-white' : 'text-gray-900' }}">{{ __('features.custom_pricing') }}</span>
                <p class="text-sm {{ $isPopular ? 'text-indigo-200' : 'text-gray-500' }} mt-1">{{ __('features.contact_for_pricing') }}</p>
            @elseif ($isFree)
                <span class="text-4xl font-bold text-gray-900">$0</span>
                <span class="text-gray-500">{{ __('features.per_month') }}</span>
            @else
                <span class="text-4xl font-bold {{ $isPopular ? 'text-white' : 'text-gray-900' }}" x-text="annual ? '${{ number_format($plan->yearly_monthly_price, 0) }}' : '${{ number_format($plan->monthly_price, 0) }}'">
                    ${{ number_format($plan->yearly_monthly_price ?? $plan->monthly_price, 0) }}
                </span>
                <span class="{{ $isPopular ? 'text-indigo-200' : 'text-gray-500' }}">{{ __('features.per_month') }}</span>
                @if ($plan->yearly_price && $savings)
                    <span x-show="annual" class="block text-sm {{ $isPopular ? 'text-indigo-200' : 'text-green-600' }} mt-1">
                        ${{ number_format($plan->yearly_price, 0) }}/{{ __('billing.year') }} ({{ __('features.save_percent', ['percent' => round($savings / ($plan->monthly_price * 12) * 100)]) }})
                    </span>
                @endif
            @endif
        </div>

        @if ($isEnterprise)
            <a href="{{ route('contact') }}?subject=enterprise" class="block w-full py-3 px-4 text-center bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-xl transition-colors">
                {{ __('billing.contact_sales') }}
            </a>
        @elseif ($isFree)
            <a href="{{ route('register') }}" class="block w-full py-3 px-4 text-center bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-xl transition-colors">
                {{ __('billing.upgrade_to', ['plan' => $plan->name]) }}
            </a>
        @else
            <a href="{{ route('register') }}?plan={{ $plan->slug }}" class="block w-full py-3 px-4 text-center {{ $isPopular ? 'bg-white hover:bg-gray-100 text-indigo-600' : 'bg-gray-100 hover:bg-gray-200 text-gray-900' }} font-semibold rounded-xl transition-colors">
                {{ __('billing.upgrade_to', ['plan' => $plan->name]) }}
            </a>
        @endif

        <ul class="mt-8 space-y-4 text-sm {{ $isPopular ? 'text-indigo-100' : 'text-gray-600' }}">
            @foreach ($features as $feature)
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 {{ $isPopular ? 'text-green-400' : 'text-green-500' }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{!! $feature !!}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
