<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-6">
            <a href="{{ route('app.invoices.index', ['clinic' => $currentClinic->slug]) }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('invoices.new_invoice') }}</h1>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Encabezado de la factura --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('invoices.invoice_details') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Paciente --}}
                    <div class="sm:col-span-2" x-data="{ open: false }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.patient') }} *</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="patientSearch"
                                   @focus="open = true" @click.outside="open = false"
                                   placeholder="{{ __('patients.search_placeholder') }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                            @if($patients && strlen($patientSearch) >= 2)
                            <div x-show="open" x-cloak
                                 class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                                @forelse($patients as $patient)
                                <button type="button"
                                        wire:click="selectPatient('{{ $patient['id'] }}', '{{ addslashes($patient['full_name']) }}')"
                                        @click="open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ $patient['full_name'] }}
                                </button>
                                @empty
                                <p class="px-4 py-3 text-sm text-gray-500">{{ __('general.no_results') }}</p>
                                @endforelse
                            </div>
                            @endif
                        </div>
                        @error('patient_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Doctor --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.doctor') }}</label>
                        <select wire:model="doctor_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— {{ __('general.optional') }} —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fecha emisión --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.issued_at') }} *</label>
                        <input type="date" wire:model="issued_at"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('issued_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Fecha límite --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.due_at') }}</label>
                        <input type="date" wire:model="due_at"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('due_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notas --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.notes') }}</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                    </div>
                </div>
            </div>

            {{-- Ítems --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.items') }}</h2>
                    <button type="button" wire:click="addItem"
                            class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('invoices.add_item') }}
                    </button>
                </div>

                @error('items') <p class="px-6 py-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($items as $i => $item)
                    <div class="p-4 grid grid-cols-12 gap-3 items-end">
                        <div class="col-span-12 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">{{ __('invoices.item_type') }}</label>
                            <select wire:model="items.{{ $i }}.type"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($itemTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label class="block text-xs text-gray-500 mb-1">{{ __('invoices.item_description') }} *</label>
                            <input type="text" wire:model="items.{{ $i }}.description"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                            @error("items.{$i}.description") <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-4 sm:col-span-1">
                            <label class="block text-xs text-gray-500 mb-1">{{ __('invoices.item_quantity') }}</label>
                            <input type="number" wire:model.live="items.{{ $i }}.quantity" step="0.01" min="0.01"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">{{ __('invoices.item_unit_price') }}</label>
                            <input type="number" wire:model.live="items.{{ $i }}.unit_price" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">{{ __('invoices.item_tax_rate') }}</label>
                            <input type="number" wire:model.live="items.{{ $i }}.tax_rate" step="0.01" min="0" max="100"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                        </div>
                        <div class="col-span-10 sm:col-span-1 flex items-end justify-end">
                            @if(count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $i }})"
                                    class="text-red-500 hover:text-red-700 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Total provisional --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <div class="text-sm">
                        <span class="text-gray-500 dark:text-gray-400 mr-3">{{ __('invoices.total') }}:</span>
                        <span class="font-bold text-gray-900 dark:text-white text-base">{{ $currency }} {{ number_format($this->itemsSubtotal, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('app.invoices.index', ['clinic' => $currentClinic->slug]) }}"
                   class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ __('invoices.invoice_created') !== 'Invoice created successfully' ? __('general.save') : __('general.save') }}
                    {{ __('general.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
