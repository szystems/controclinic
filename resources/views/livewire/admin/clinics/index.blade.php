<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.clinics_management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filters --}}
            <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="{{ __('admin.search_clinics') }}"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <select wire:model.live="filterPlan"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('admin.all_plans') }}</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->slug }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="filterStatus"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('admin.all_statuses') }}</option>
                            <option value="active">{{ __('general.active') }}</option>
                            <option value="trial">{{ __('admin.trial') }}</option>
                            <option value="suspended">{{ __('admin.suspended') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.clinic_name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.owner') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.plan') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('general.status') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('patients.title') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.users') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.created') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($clinics as $clinic)
                                <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" @click="window.location.href='{{ route('admin.clinics.show', $clinic) }}'">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $clinic->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $clinic->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($clinic->owner)
                                            <div class="text-sm text-gray-900 dark:text-white">{{ $clinic->owner->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $clinic->owner->email }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($clinic->plan_type === 'free') bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300
                                            @elseif($clinic->plan_type === 'solo') bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                                            @elseif($clinic->plan_type === 'group') bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                                            @elseif($clinic->plan_type === 'enterprise') bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300
                                            @else bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300
                                            @endif">
                                            {{ $clinic->plan?->name ?? __('admin.plan_type_' . $clinic->plan_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($clinic->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{{ __('general.active') }}</span>
                                        @elseif($clinic->status === 'trial')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('admin.trial') }}</span>
                                        @elseif($clinic->status === 'suspended')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('admin.suspended') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __("admin.plan_type_{$clinic->status}") }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">{{ $clinic->patients_count }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">{{ $clinic->users_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $clinic->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-right" @click.stop>
                                        <a href="{{ route('admin.clinics.show', $clinic) }}" wire:navigate
                                           class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                            {{ __('admin.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('admin.no_clinics_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($clinics->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $clinics->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
