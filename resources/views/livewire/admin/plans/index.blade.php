<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.plans_management') }}
        </h2>
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
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $plan->name }}
                                                    @if($plan->is_popular)
                                                        <span class="ml-1 px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded">{{ __('admin.popular') }}</span>
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
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.plans.edit', $plan) }}" wire:navigate
                                           class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                            {{ __('general.edit') }}
                                        </a>
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
