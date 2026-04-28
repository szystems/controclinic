<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('billing.title') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">{{ __('billing.subtitle') }}</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif
        @if (session()->has('warning'))
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <p class="text-sm text-amber-700 dark:text-amber-300">{{ session('warning') }}</p>
            </div>
        @endif

        <!-- Current Plan Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('billing.current_plan') }}</h2>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white capitalize">{{ $this->currentPlan }}</span>
                        @if ($this->currentPlan === 'free')
                            <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">{{ __('billing.free_plan') }}</span>
                        @elseif ($this->isSubscribed)
                            <span class="px-2.5 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">{{ __('billing.active') }}</span>
                        @endif
                    </div>

                    @if ($this->isSubscribed && $this->subscription)
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            @if ($this->subscription->onGracePeriod())
                                {{ __('billing.cancels_at', ['date' => $this->subscription->ends_at->format('d/m/Y')]) }}
                            @else
                                {{ __('billing.renews_at', ['date' => $this->subscription->ends_at?->format('d/m/Y') ?? '—']) }}
                            @endif
                        </p>
                    @endif
                </div>

                <div class="flex gap-2">
                    @if ($this->isSubscribed)
                        @if ($this->subscription->canceled() && $this->subscription->onGracePeriod())
                            <button wire:click="resumeSubscription" wire:confirm="{{ __('billing.confirm_resume') }}"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                                {{ __('billing.resume') }}
                            </button>
                        @else
                            <button wire:click="redirectToCustomerPortal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                {{ __('billing.manage_billing') }}
                            </button>
                            <button wire:click="cancelSubscription" wire:confirm="{{ __('billing.confirm_cancel') }}"
                                class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:bg-gray-700 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                {{ __('billing.cancel') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Usage Stats -->
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $limits = $clinic->getPlanLimits();
                    $patientCount = $clinic->patients()->count();
                    $appointmentCount = $clinic->appointments()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
                    $doctorCount = $clinic->doctors()->count();
                @endphp
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('billing.patients') }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $patientCount }}<span class="text-sm font-normal text-gray-400"> / {{ $limits['max_patients'] ?? '∞' }}</span>
                    </p>
                    @if ($limits['max_patients'])
                        <div class="mt-1 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $patientCount / $limits['max_patients'] > 0.8 ? 'bg-amber-500' : 'bg-primary' }}"
                                 style="width: {{ min(100, ($patientCount / $limits['max_patients']) * 100) }}%"></div>
                        </div>
                    @endif
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('billing.appointments_month') }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $appointmentCount }}<span class="text-sm font-normal text-gray-400"> / {{ $limits['max_appointments_per_month'] ?? '∞' }}</span>
                    </p>
                    @if ($limits['max_appointments_per_month'])
                        <div class="mt-1 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $appointmentCount / $limits['max_appointments_per_month'] > 0.8 ? 'bg-amber-500' : 'bg-primary' }}"
                                 style="width: {{ min(100, ($appointmentCount / $limits['max_appointments_per_month']) * 100) }}%"></div>
                        </div>
                    @endif
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('billing.doctors') }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $doctorCount }}<span class="text-sm font-normal text-gray-400"> / {{ $limits['max_doctors'] ?? '∞' }}</span>
                    </p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('billing.plan_type') }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white capitalize">{{ $this->currentPlan }}</p>
                </div>
            </div>
        </div>

        <!-- Plans Section (only if not enterprise) -->
        @if ($this->currentPlan !== 'enterprise')
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('billing.available_plans') }}</h2>

                    <!-- Billing Cycle Toggle -->
                    <div class="inline-flex items-center bg-gray-100 dark:bg-gray-700 rounded-full p-1">
                        <button wire:click="$set('billingCycle', 'monthly')"
                            class="px-4 py-1.5 rounded-full text-sm font-medium transition
                            {{ $billingCycle === 'monthly' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ __('billing.monthly') }}
                        </button>
                        <button wire:click="$set('billingCycle', 'yearly')"
                            class="px-4 py-1.5 rounded-full text-sm font-medium transition
                            {{ $billingCycle === 'yearly' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ __('billing.yearly') }}
                            <span class="ml-1 text-xs text-green-600 dark:text-green-400 font-semibold">-20%</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-8">
                @foreach ($this->plans as $plan)
                    <x-plan-card
                        :plan="$plan"
                        context="billing"
                        :billingCycle="$billingCycle"
                        :currentPlan="$this->currentPlan"
                        :isSubscribed="$this->isSubscribed"
                        onCheckout="checkout"
                        onChangePlan="changePlan"
                    />
                @endforeach
            </div>

            <!-- Free plan note -->
            @if ($this->currentPlan !== 'free')
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('billing.downgrade_note') }}
                    </p>
                </div>
            @endif

            {{-- Paddle Checkout via JS --}}
            <div x-data x-on:open-paddle-checkout.window="
                Paddle.Checkout.open({
                    transactionId: $event.detail.transactionId,
                    settings: {
                        allowLogout: false,
                        displayMode: 'overlay',
                        theme: 'light',
                        locale: '{{ app()->getLocale() }}'
                    }
                });
            "></div>
        @endif

        <!-- Transaction History -->
        @if ($this->isSubscribed)
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('billing.transaction_history') }}</h2>
                    <button wire:click="redirectToCustomerPortal"
                        class="text-sm text-primary hover:underline">
                        {{ __('billing.view_all_invoices') }}
                    </button>
                </div>

                @php
                    $transactions = $clinic->transactions()->take(5);
                @endphp

                @if ($transactions->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ __('billing.date') }}</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ __('billing.invoice') }}</th>
                                    <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ __('billing.status') }}</th>
                                    <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">{{ __('billing.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="py-3 px-4 text-gray-900 dark:text-white">{{ $transaction->billed_at?->format('d/m/Y') }}</td>
                                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $transaction->invoice_number ?? '—' }}</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $transaction->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                                {{ __('billing.status_' . $transaction->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right text-gray-900 dark:text-white font-medium">${{ number_format($transaction->total / 100, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('billing.no_transactions') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
