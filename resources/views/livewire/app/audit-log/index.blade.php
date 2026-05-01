<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('audit_log.title') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('audit_log.subtitle') }}</p>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

                {{-- Event filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('audit_log.filter_event') }}
                    </label>
                    <select wire:model.live="filterEvent"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('audit_log.all_events') }}</option>
                        <option value="created">{{ __('audit_log.events.created') }}</option>
                        <option value="updated">{{ __('audit_log.events.updated') }}</option>
                        <option value="deleted">{{ __('audit_log.events.deleted') }}</option>
                        <option value="permissions_updated">{{ __('audit_log.events.permissions_updated') }}</option>
                        <option value="permissions_restored">{{ __('audit_log.events.permissions_restored') }}</option>
                        <option value="ownership_transferred">{{ __('audit_log.events.ownership_transferred') }}</option>
                        <option value="data_exported">{{ __('audit_log.events.data_exported') }}</option>
                    </select>
                </div>

                {{-- Subject filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('audit_log.filter_subject') }}
                    </label>
                    <select wire:model.live="filterSubject"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('audit_log.all_subjects') }}</option>
                        @foreach(array_keys(__('audit_log.subjects')) as $key)
                            <option value="{{ $key }}">{{ __('audit_log.subjects.' . $key) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- User filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('audit_log.filter_user') }}
                    </label>
                    <select wire:model.live="filterUser"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('audit_log.all_users') }}</option>
                        @foreach($clinicUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date from --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('audit_log.filter_date_from') }}
                    </label>
                    <input type="date" wire:model.live="dateFrom"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                {{-- Date to --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('audit_log.filter_date_to') }}
                    </label>
                    <input type="date" wire:model.live="dateTo"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                {{-- Clear button --}}
                <div class="flex items-end">
                    @if($hasFilters)
                    <button type="button" wire:click="clearFilters"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ __('audit_log.clear_filters') }}
                    </button>
                    @endif
                </div>

            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($activities->isEmpty())
                <div class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('audit_log.no_results') }}</p>
                    @if($hasFilters)
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('audit_log.no_results_hint') }}</p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('audit_log.col_date') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('audit_log.col_user') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('audit_log.col_event') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('audit_log.col_entity') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('audit_log.col_changes') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($activities as $activity)
                                @php
                                    $subjectClass = $activity->subject_type
                                        ? class_basename($activity->subject_type)
                                        : null;
                                    $eventKey = 'audit_log.events.' . $activity->description;
                                    $eventLabel = __($eventKey) !== $eventKey
                                        ? __($eventKey)
                                        : $activity->description;
                                    $properties = $activity->properties->toArray();
                                    $hasDetails = ! empty($properties);
                                @endphp
                                <tr x-data="{ open: false }"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                    {{-- Date --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $activity->created_at->format('d/m/Y') }}
                                        </span>
                                        <span class="block text-xs text-gray-400 dark:text-gray-500">
                                            {{ $activity->created_at->format('H:i') }}
                                        </span>
                                    </td>

                                    {{-- Causer --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($activity->causer)
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $activity->causer->name }}
                                            </span>
                                            <span class="block text-xs text-gray-400 dark:text-gray-500">
                                                {{ $activity->causer->email }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500 italic">
                                                {{ __('audit_log.unknown_user') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Event --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $eventColor = match($activity->description) {
                                                'created' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                                'updated' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                                'deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                                'permissions_updated', 'permissions_restored' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                                                'ownership_transferred' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                                'data_exported' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                                                default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $eventColor }}">
                                            {{ $eventLabel }}
                                        </span>
                                    </td>

                                    {{-- Subject --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($subjectClass)
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ __('audit_log.subjects.' . $subjectClass, [], null) ?? $subjectClass }}
                                            </span>
                                            @if($activity->subject)
                                                <span class="block text-xs text-gray-400 dark:text-gray-500 font-mono">
                                                    {{ substr($activity->subject_id, 0, 8) }}…
                                                </span>
                                            @else
                                                <span class="block text-xs text-gray-400 dark:text-gray-500 italic">
                                                    {{ __('audit_log.unknown_entity') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </td>

                                    {{-- Changes --}}
                                    <td class="px-4 py-3">
                                        @if($hasDetails)
                                            <button type="button"
                                                @click="open = !open"
                                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                                <span x-show="!open">{{ __('audit_log.see_details') }}</span>
                                                <span x-show="open" x-cloak>{{ __('audit_log.hide_details') }}</span>
                                            </button>
                                            <div x-show="open" x-cloak class="mt-2 text-xs font-mono space-y-2">
                                                @if(isset($properties['old']) && isset($properties['attributes']))
                                                    {{-- updated event with old/new --}}
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <p class="font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('audit_log.attributes_before') }}</p>
                                                            @foreach($properties['old'] as $key => $val)
                                                                <div class="text-gray-600 dark:text-gray-400">
                                                                    <span class="text-gray-400">{{ $key }}:</span>
                                                                    <span class="bg-red-50 dark:bg-red-900/20 px-1 rounded">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div>
                                                            <p class="font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('audit_log.attributes_after') }}</p>
                                                            @foreach($properties['attributes'] as $key => $val)
                                                                <div class="text-gray-600 dark:text-gray-400">
                                                                    <span class="text-gray-400">{{ $key }}:</span>
                                                                    <span class="bg-green-50 dark:bg-green-900/20 px-1 rounded">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif(isset($properties['attributes']))
                                                    {{-- created event --}}
                                                    @foreach($properties['attributes'] as $key => $val)
                                                        <div class="text-gray-600 dark:text-gray-400">
                                                            <span class="text-gray-400">{{ $key }}:</span>
                                                            {{ is_array($val) ? json_encode($val) : $val }}
                                                        </div>
                                                    @endforeach
                                                @else
                                                    {{-- custom properties (permissions_updated, data_exported, etc.) --}}
                                                    @foreach($properties as $key => $val)
                                                        <div class="text-gray-600 dark:text-gray-400">
                                                            <span class="text-gray-400">{{ $key }}:</span>
                                                            {{ is_array($val) ? json_encode($val) : $val }}
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">{{ __('audit_log.no_changes') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($activities->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $activities->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
