<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.subscribers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div>
                        <select wire:model.live="account"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="live">{{ __('admin.subscribers_live') }}</option>
                            <option value="previous">{{ __('admin.subscribers_previous') }}</option>
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="status"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('admin.all_statuses') }}</option>
                            <option value="active">{{ __('admin.sub_status_active') }}</option>
                            <option value="past_due">{{ __('admin.sub_status_past_due') }}</option>
                            <option value="paused">{{ __('admin.sub_status_paused') }}</option>
                            <option value="canceled">{{ __('admin.sub_status_canceled') }}</option>
                            <option value="trialing">{{ __('admin.sub_status_trialing') }}</option>
                        </select>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ $account === 'previous' ? __('admin.subscribers_previous_hint') : __('admin.subscribers_live_hint') }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.clinic_name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.plan') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('general.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.cancels_at') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.last_payment') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($subscriptions as $subscription)
                                @php
                                    $clinic = $subscription->billable;
                                    $priceId = $subscription->items->first()?->price_id;
                                    $plan = $priceId ? ($plansByPrice[$priceId] ?? null) : null;
                                    $payment = $lastPayments->get($subscription->paddle_id);
                                @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        @if($clinic)
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $clinic->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $clinic->owner?->email ?? $clinic->email }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $plan['name'] ?? '—' }}</div>
                                        @if($plan)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $plan['cycle'] === 'yearly' ? __('admin.yr') : __('admin.mo') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                               ($subscription->status === 'past_due' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                               ($subscription->status === 'paused' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                               'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                                            {{ __('admin.sub_status_'.$subscription->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        @if($payment)
                                            ${{ number_format(((int) $payment->total) / 100, 2) }}
                                            <span class="block text-xs">{{ $payment->billed_at->format('d/m/Y') }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($clinic)
                                            <a href="{{ route('admin.clinics.show', $clinic) }}" wire:navigate
                                               class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                {{ __('admin.view') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ $account === 'previous' ? __('admin.subscribers_previous_empty') : __('admin.subscribers_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($subscriptions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
