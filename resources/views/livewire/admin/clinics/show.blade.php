<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $clinic->name }}
            </h2>
            <a href="{{ route('admin.clinics.index') }}" wire:navigate
               class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                ← {{ __('admin.back_to_clinics') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Status & Actions --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $clinic->name }}</h3>
                            @if($clinic->status === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{{ __('general.active') }}</span>
                            @elseif($clinic->status === 'trial')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('admin.trial') }}</span>
                            @elseif($clinic->status === 'suspended')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('admin.suspended') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $clinic->slug }} &middot; {{ $clinic->email }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if($clinic->status !== 'suspended')
                            <button wire:click="suspend" wire:confirm="{{ __('admin.confirm_suspend') }}"
                                    class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md hover:bg-red-100 dark:hover:bg-red-900/50">
                                {{ __('admin.suspend') }}
                            </button>
                        @endif
                        @if($clinic->status === 'suspended')
                            <button wire:click="activate"
                                    class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md hover:bg-green-100 dark:hover:bg-green-900/50">
                                {{ __('admin.activate') }}
                            </button>
                        @endif
                        <button wire:click="extendTrial(14)"
                                class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900/50">
                            {{ __('admin.extend_trial') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Clinic Info --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Stats --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('patients.title') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $patientsCount }}</p>
                            @if($clinic->max_patients)
                                <p class="text-xs text-gray-400">/ {{ $clinic->max_patients }}</p>
                            @endif
                        </div>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.appointments_month') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $appointmentsThisMonth }}</p>
                            @if($clinic->max_appointments_per_month)
                                <p class="text-xs text-gray-400">/ {{ $clinic->max_appointments_per_month }}</p>
                            @endif
                        </div>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.users') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clinic->users->count() }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.plan') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $clinic->plan?->name ?? __('admin.plan_type_' . $clinic->plan_type) }}</p>
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('admin.clinic_details') }}</h4>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('admin.owner') }}</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $clinic->owner?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $clinic->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('admin.phone') }}</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $clinic->phone ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('admin.city') }}</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $clinic->city ?? '—' }}, {{ $clinic->country ?? '' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Timezone</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $clinic->timezone ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('admin.created') }}</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $clinic->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            @if($clinic->trial_ends_at)
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('admin.trial_ends') }}</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $clinic->trial_ends_at->format('d/m/Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Limits --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('admin.current_limits') }}</h4>
                        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('patients.title') }}</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $clinic->max_patients ?? '∞' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('admin.appointments_per_month') }}</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $clinic->max_appointments_per_month ?? '∞' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('general.doctors') }}</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $clinic->max_doctors ?? '∞' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('general.staff') }}</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $clinic->max_staff ?? '∞' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Manual Plan Override --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-2 {{ $clinic->is_manual_plan ? 'border-amber-400 dark:border-amber-600' : 'border-transparent' }}">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                                </svg>
                                {{ __('admin.manual_plan') }}
                            </h4>
                            @if($clinic->is_manual_plan)
                                <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-full">
                                    {{ __('general.active') }}
                                </span>
                            @endif
                        </div>

                        @if($clinic->is_manual_plan)
                            <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                <p class="text-xs text-amber-800 dark:text-amber-200 font-medium">{{ __('admin.manual_plan_active_info') }}</p>
                                <p class="text-xs text-amber-600 dark:text-amber-300 mt-1">
                                    <span class="font-semibold">{{ __('admin.reason') }}:</span> {{ $clinic->manual_plan_reason }}
                                </p>
                            </div>
                            <button wire:click="removeManualPlan" wire:confirm="{{ __('admin.confirm_remove_manual') }}"
                                    class="w-full px-3 py-2 text-xs font-medium text-amber-700 bg-amber-50 dark:text-amber-400 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-md hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                {{ __('admin.remove_manual_plan') }}
                            </button>
                        @else
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('admin.manual_plan_description') }}</p>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.reason') }}</label>
                                    <textarea wire:model="manualPlanReason" rows="2" placeholder="{{ __('admin.manual_plan_reason_placeholder') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"></textarea>
                                    @error('manualPlanReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($plans as $plan)
                                        <button wire:click="assignManualPlan({{ $plan->id }})"
                                                class="w-full text-left px-3 py-2 text-xs rounded-md border border-amber-200 dark:border-amber-800 hover:bg-amber-50 dark:hover:bg-amber-900/30 text-gray-700 dark:text-gray-300 transition">
                                            <div class="font-medium">{{ $plan->name }}</div>
                                            <div class="text-xs text-gray-400">
                                                @if($plan->is_free) {{ __('admin.free') }}
                                                @elseif($plan->is_enterprise) {{ __('admin.custom') }}
                                                @else ${{ $plan->monthly_price }}/{{ __('admin.mo') }}
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Paddle Subscription Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            {{ __('admin.paddle_subscription') }}
                        </h4>

                        @if($subscriptionInfo)
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('general.status') }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $subscriptionInfo['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                           ($subscriptionInfo['status'] === 'trialing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                           ($subscriptionInfo['status'] === 'past_due' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                           'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')) }}">
                                        {{ __('admin.sub_status_' . $subscriptionInfo['status']) }}
                                    </span>
                                </div>

                                @if($subscriptionInfo['plan_name'])
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.paying_plan') }}</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $subscriptionInfo['plan_name'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.amount') }}</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            ${{ $subscriptionInfo['price'] }}/{{ $subscriptionInfo['cycle'] === 'yearly' ? __('admin.yr') : __('admin.mo') }}
                                        </span>
                                    </div>
                                @endif

                                @if($subscriptionInfo['ends_at'])
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.cancels_at') }}</span>
                                        <span class="text-xs text-red-600 dark:text-red-400">{{ \Carbon\Carbon::parse($subscriptionInfo['ends_at'])->format('d/m/Y') }}</span>
                                    </div>
                                @endif

                                @if($clinic->is_manual_plan && $subscriptionInfo['plan_name'])
                                    <div class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 rounded text-xs text-amber-700 dark:text-amber-300">
                                        {{ __('admin.pays_but_has', ['pays' => $subscriptionInfo['plan_name'], 'has' => $clinic->plan->name]) }}
                                    </div>
                                @endif

                                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs text-gray-400 font-mono">{{ $subscriptionInfo['paddle_id'] }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <svg class="mx-auto w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('admin.no_paddle_subscription') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Users --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('admin.users') }} ({{ $clinic->users->count() }})</h4>
                        <ul class="space-y-3">
                            @foreach($clinic->users as $user)
                                <li class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
