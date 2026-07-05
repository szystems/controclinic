<div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('admin.plans_management') }}
            </h2>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('admin.plans.create') }}" wire:navigate
                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    {{ __('admin.create_plan') }}
                </a>
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" wire:model.live="showInactive"
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    {{ __('admin.show_inactive_plans') }}
                </label>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.plan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.price') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.limits') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.clinics') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('general.status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($plans as $plan)
                                <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" @click="window.location.href='{{ route('admin.plans.edit', $plan) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white flex flex-wrap items-center gap-1">
                                                    {{ $plan->name }}
                                                    @if($plan->is_popular)
                                                        <span class="px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded">{{ __('admin.popular') }}</span>
                                                    @endif
                                                    @if($plan->is_enterprise)
                                                        <span class="px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded">{{ __('admin.enterprise') }}</span>
                                                    @endif
                                                    @if($plan->is_private)
                                                        <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded">{{ __('admin.badge_private') }}</span>
                                                    @endif
                                                    @if($plan->requires_code)
                                                        <span class="px-1.5 py-0.5 text-xs bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded">{{ __('admin.badge_code') }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $plan->slug }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($plan->is_free)
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.free') }}</span>
                                        @elseif($plan->is_enterprise)
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.custom') }}</span>
                                        @else
                                            <div class="text-sm text-gray-900 dark:text-white">${{ $plan->monthly_price }}/{{ __('admin.mo') }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">${{ $plan->yearly_price }}/{{ __('admin.yr') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                                            <div>{{ __('patients.title') }}: {{ $plan->max_patients ?? '∞' }}</div>
                                            <div>{{ __('general.appointments_this_month') }}: {{ $plan->max_appointments_per_month ?? '∞' }}</div>
                                            <div>{{ __('general.doctors') }}: {{ $plan->max_doctors ?? '∞' }}</div>
                                            <div>{{ __('general.staff') }}: {{ $plan->max_staff ?? '∞' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->clinics_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($plan->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                {{ __('general.active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ __('general.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right" @click.stop>
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('admin.plans.edit', $plan) }}" wire:navigate
                                               class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                {{ __('general.edit') }}
                                            </a>
                                            @if($plan->canBeDeleted())
                                                <button type="button"
                                                        wire:click="askDeletePlan({{ $plan->id }})"
                                                        class="text-sm text-red-600 hover:text-red-500 dark:text-red-400">
                                                    {{ __('general.delete') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($confirmDeletePlanId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 dark:bg-black/70">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('admin.delete_plan') }}</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ __('admin.plan_delete_confirm_named', ['name' => $confirmDeletePlanName]) }}
                </p>
                <div class="flex gap-2 justify-end">
                    <button type="button" wire:click="cancelDeletePlan"
                            class="text-sm px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('general.cancel') }}
                    </button>
                    <button type="button" wire:click="deletePlan"
                            class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white">
                        {{ __('admin.delete_plan') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
