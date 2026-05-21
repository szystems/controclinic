<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('app.settings.index', $clinicSlug) }}" wire:navigate
                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    ← {{ __('settings.title') }}
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mt-1">
                    {{ __('templates.title') }}
                </h2>
            </div>
            <button wire:click="create"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('templates.new') }}
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if (session()->has('success'))
                <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/50 p-4 flex items-center gap-3">
                    <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Empty state --}}
            @if($this->templates->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('templates.empty') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('templates.empty_hint') }}</p>
                    <button wire:click="create" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('templates.new') }}
                    </button>
                </div>
            @else
                {{-- Templates grouped by record_type --}}
                @php
                    $grouped = $this->templates->groupBy('record_type');
                @endphp
                <div class="space-y-6">
                    @foreach($grouped as $type => $typeTemplates)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('records.type_' . $type) }}
                                </h3>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($typeTemplates as $template)
                                    <li class="flex items-center justify-between px-5 py-4 gap-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-sm text-gray-900 dark:text-white">{{ $template->name }}</span>
                                                @if($template->is_default)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                                        {{ __('templates.default_badge') }}
                                                    </span>
                                                @endif
                                                @if($template->specialty)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ $template->specialty }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($template->chief_complaint)
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $template->chief_complaint }}</p>
                                            @endif
                                            @if($template->createdBy)
                                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                                    {{ __('templates.created_by') }}: {{ $template->createdBy->name }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button wire:click="edit('{{ $template->id }}')"
                                                    class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            @if($confirmDeleteId === $template->id)
                                                <span class="text-xs text-red-600 dark:text-red-400">{{ __('templates.confirm_delete') }}</span>
                                                <button wire:click="deleteTemplate" class="px-2 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition">{{ __('common.yes') }}</button>
                                                <button wire:click="cancelDelete" class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded transition">{{ __('common.no') }}</button>
                                            @else
                                                <button wire:click="confirmDelete('{{ $template->id }}')"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Create / Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-screen items-end sm:items-center justify-center p-4">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModal"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $editingId ? __('templates.edit') : __('templates.new') }}
                        </h3>
                        <button wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-5">
                        {{-- Name + Type --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.name') }} *
                                </label>
                                <input type="text" wire:model="name" maxlength="255"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                                       placeholder="{{ __('templates.name') }}">
                                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.record_type') }} *
                                </label>
                                <select wire:model="recordType"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                    @foreach($this->recordTypes as $type)
                                        <option value="{{ $type }}">{{ __('records.type_' . $type) }}</option>
                                    @endforeach
                                </select>
                                @error('recordType') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Specialty --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('templates.specialty') }}
                            </label>
                            <input type="text" wire:model="specialty" maxlength="100"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                                   placeholder="{{ __('templates.specialty') }}">
                        </div>

                        {{-- SOAP Fields --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.chief_complaint') }}
                                </label>
                                <textarea wire:model="chiefComplaint" rows="2" maxlength="2000"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.present_illness') }}
                                </label>
                                <textarea wire:model="presentIllness" rows="3" maxlength="5000"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.physical_examination') }}
                                </label>
                                <textarea wire:model="physicalExamination" rows="3" maxlength="5000"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.assessment') }}
                                </label>
                                <textarea wire:model="assessment" rows="3" maxlength="5000"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('templates.plan') }}
                                </label>
                                <textarea wire:model="plan" rows="3" maxlength="5000"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm resize-none"></textarea>
                            </div>
                        </div>

                        {{-- Default toggle --}}
                        <div class="flex items-start gap-3 p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                            <div class="pt-0.5">
                                <input type="checkbox" wire:model="isDefault" id="isDefault"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="isDefault" class="block text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    {{ __('templates.is_default') }}
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('templates.is_default_hint') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                        <button wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            {{ __('templates.cancel') }}
                        </button>
                        <button wire:click="save"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            {{ __('templates.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
