<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Stat Cards Row 1 - Core Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.total_clinics') }}</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalClinics }}</div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ $newClinicsThisMonth }} {{ __('admin.this_month') }}</div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.active_subscriptions') }}</div>
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $activeSubscriptions }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.paying_in_paddle') }}</div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.revenue_this_month') }}</div>
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ $revenueThisMonth['total'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ $revenueThisMonth['count'] }} {{ __('admin.transactions_count') }}
                        @if((float)$revenueLastMonth['total'] > 0)
                            @php
                                $diff = (float)$revenueThisMonth['total'] - (float)$revenueLastMonth['total'];
                                $pct = round(($diff / (float)$revenueLastMonth['total']) * 100, 1);
                            @endphp
                            <span class="{{ $pct >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ({{ $pct >= 0 ? '+' : '' }}{{ $pct }}%)
                            </span>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.conversion_rate') }}</div>
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $conversionRate }}%</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.free_to_paid') }}</div>
                </div>
            </div>

            {{-- Stat Cards Row 2 - Secondary Metrics --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('admin.active_clinics') }}</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $activeClinics }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('admin.trial_clinics') }}</div>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $trialClinics }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('admin.suspended_clinics') }}</div>
                    <div class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $suspendedClinics }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('admin.total_users') }}</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalUsers }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('admin.manual_plans') }}</div>
                    <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $manualPlanClinics }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('admin.past_due') }}</div>
                    <div class="text-xl font-bold {{ $subscriptionBreakdown['past_due'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} mt-1">
                        {{ $subscriptionBreakdown['past_due'] }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Subscription Breakdown --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.subscription_breakdown') }}</h3>
                    <div class="space-y-3">
                        @php
                            $subColors = [
                                'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'trialing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'past_due' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'paused' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'canceled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $totalSubs = array_sum($subscriptionBreakdown);
                        @endphp
                        @foreach($subscriptionBreakdown as $status => $count)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subColors[$status] }}">
                                        {{ __('admin.sub_status_' . $status) }}
                                    </span>
                                    @if($totalSubs > 0)
                                        <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ str_replace('text-', 'bg-', explode(' ', $subColors[$status])[0]) }}"
                                                 style="width: {{ round(($count / $totalSubs) * 100) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $count }}</span>
                            </div>
                        @endforeach
                        <div class="border-t dark:border-gray-700 pt-2 mt-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.total_subscriptions') }}</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $totalSubs }}</span>
                        </div>
                    </div>
                </div>

                {{-- Clinics by Plan --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.clinics_by_plan') }}</h3>
                    <div class="space-y-3">
                        @foreach($activePlans as $plan)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $plan->is_free ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' :
                                           ($plan->slug === 'solo' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                           ($plan->slug === 'group' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                           'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200')) }}">
                                        {{ $plan->name }}
                                    </span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $plan->clinics_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Revenue Comparison --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.revenue') }}</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                            <div>
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('admin.revenue_this_month') }}</div>
                                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">${{ $revenueThisMonth['total'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $revenueThisMonth['count'] }} {{ __('admin.transactions_count') }}</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('admin.revenue_last_month') }}</div>
                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">${{ $revenueLastMonth['total'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $revenueLastMonth['count'] }} {{ __('admin.transactions_count') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Plans Overview --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.plans') }}</h3>
                        <a href="{{ route('admin.plans.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">
                            {{ __('admin.manage_plans') }} →
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($activePlans as $plan)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $plan->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        @if($plan->is_free) $0
                                        @elseif($plan->is_enterprise) {{ __('admin.custom') }}
                                        @else ${{ $plan->monthly_price }}/{{ __('admin.mo') }}
                                        @endif
                                    </span>
                                </div>
                                <a href="{{ route('admin.plans.edit', $plan) }}" wire:navigate
                                   class="text-xs text-indigo-600 hover:text-indigo-500">{{ __('general.edit') }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('admin.recent_transactions') }}</h3>
                    @if($recentTransactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.clinic_name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.amount') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('general.status') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.invoice') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentTransactions as $transaction)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                {{ $transaction->billable?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                                                ${{ number_format((int)$transaction->total / 100, 2) }}
                                                <span class="text-xs text-gray-500 font-normal">{{ $transaction->currency }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $txStatusColors = [
                                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                        'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                        'billed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                        'past_due' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                        'canceled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    ];
                                                    $txColor = $txStatusColors[$transaction->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $txColor }}">
                                                    {{ __('admin.tx_status_' . $transaction->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $transaction->invoice_number ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $transaction->billed_at?->diffForHumans() ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.no_transactions') }}</p>
                    @endif
                </div>
            </div>

            {{-- Recent Clinics --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.recent_clinics') }}</h3>
                        <a href="{{ route('admin.clinics.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">
                            {{ __('admin.view_all') }} →
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.clinic_name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.owner') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.plan') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('general.status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.created') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recentClinics as $clinic)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.clinics.show', $clinic) }}" wire:navigate
                                               class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                                {{ $clinic->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $clinic->owner?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                                {{ $clinic->plan?->name ?? __('admin.plan_type_' . $clinic->plan_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                {{ $clinic->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                                   ($clinic->status === 'trial' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                                   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                                {{ $clinic->status === 'active' ? __('general.active') : ($clinic->status === 'trial' ? __('admin.trial') : __('admin.suspended')) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $clinic->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
