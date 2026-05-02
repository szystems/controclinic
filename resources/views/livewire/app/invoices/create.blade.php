<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('app.invoices.index', ['clinic' => $currentClinic->slug]) }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('invoices.new_invoice') }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('invoices.currency') }}: <span class="font-mono font-semibold">{{ $currency }}</span></p>
                </div>
            </div>
            @if($appointmentId)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('invoices.from_appointment') ?? 'Desde cita' }}
                </span>
            @endif
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Encabezado --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.invoice_details') }}</h2>

                {{-- Paciente --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.patient') }} <span class="text-red-500">*</span></label>

                    @if($patient_id && $patientName)
                        <div class="flex items-center justify-between px-4 py-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($patientName, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $patientName }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('invoices.patient') }} #{{ substr($patient_id, 0, 8) }}</p>
                                </div>
                            </div>
                            @if(!$appointmentId)
                                <button type="button" wire:click="clearPatient"
                                        class="text-gray-400 hover:text-red-600 transition" title="{{ __('general.cancel') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
                            <div class="relative">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                                <input type="text" wire:model.live.debounce.300ms="patientSearch"
                                       @focus="open = true" @click.outside="open = false"
                                       placeholder="{{ __('patients.search_placeholder') }}"
                                       class="w-full pl-9 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                            </div>
                            @if(strlen($patientSearch) >= 2)
                                <div x-show="open" x-cloak x-transition.opacity
                                     class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto">
                                    @forelse($patients as $patient)
                                        <button type="button"
                                                wire:click="selectPatient('{{ $patient['id'] }}', @js($patient['full_name']))"
                                                @click="open = false"
                                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 last:border-b-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient['full_name'] }}</p>
                                            @if(!empty($patient['email']) || !empty($patient['phone']))
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $patient['email'] ?? '' }}
                                                    @if(!empty($patient['email']) && !empty($patient['phone'])) · @endif
                                                    {{ $patient['phone'] ?? '' }}
                                                </p>
                                            @endif
                                        </button>
                                    @empty
                                        <p class="px-4 py-3 text-sm text-gray-500">{{ __('general.no_results') }}</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif
                    @error('patient_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Doctor --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.doctor') }}</label>
                    @if($doctors->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">{{ __('invoices.no_doctors') ?? 'No hay doctores registrados en la clínica.' }}</p>
                    @else
                        <select wire:model="doctor_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— {{ __('general.optional') ?? 'Opcional' }} —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('doctor_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Fechas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.issued_at') }} <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="issued_at"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('issued_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.due_at') }}</label>
                        <input type="date" wire:model="due_at"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('due_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.notes') }}</label>
                    <textarea wire:model="notes" rows="2"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Ítems --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.items') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($items) }} {{ __('invoices.items') }}</p>
                    </div>
                    <button type="button" wire:click="addItem"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('invoices.add_item') }}
                    </button>
                </div>

                @error('items') <p class="px-6 py-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($items as $i => $item)
                        @php
                            $qty = (float) ($item['quantity'] ?? 0);
                            $price = (float) ($item['unit_price'] ?? 0);
                            $disc = (float) ($item['discount_amount'] ?? 0);
                            $rate = (float) ($item['tax_rate'] ?? 0);
                            $base = $qty * $price;
                            $net = max($base - $disc, 0);
                            $itemTotal = round($net + ($net * $rate / 100), 2);
                        @endphp
                        <div class="p-5 space-y-4 hover:bg-gray-50/50 dark:hover:bg-gray-900/20" wire:key="item-{{ $i }}">
                            {{-- Header de la línea --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $i + 1 }}</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('invoices.item') ?? 'Línea' }} #{{ $i + 1 }}</span>
                                </div>
                                @if(count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $i }})"
                                            class="inline-flex items-center text-xs text-red-600 hover:text-red-800 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                        {{ __('invoices.remove_item') }}
                                    </button>
                                @endif
                            </div>

                            {{-- Tipo --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_type') }}</label>
                                <select wire:model="items.{{ $i }}.type"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($itemTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Descripción --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_description') }} <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="items.{{ $i }}.description"
                                       placeholder="{{ __('invoices.item_description') }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                @error("items.{$i}.description") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cantidad --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_quantity') }}</label>
                                <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.quantity" step="0.01" min="0.01"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                            </div>

                            {{-- Precio unitario --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_unit_price') }}</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-mono text-gray-500 dark:text-gray-400">{{ $currency }}</span>
                                    <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.unit_price" step="0.01" min="0"
                                           class="w-full pl-14 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                </div>
                            </div>

                            {{-- Descuento --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_discount') }}</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-mono text-gray-500 dark:text-gray-400">{{ $currency }}</span>
                                    <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.discount_amount" step="0.01" min="0"
                                           class="w-full pl-14 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                </div>
                            </div>

                            {{-- Impuesto --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_tax_rate') }}</label>
                                <div class="relative">
                                    <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.tax_rate" step="0.01" min="0" max="100"
                                           class="w-full pr-8 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>

                            {{-- Total línea (read-only) --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('invoices.item_total') }}</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ $currency }} {{ number_format($itemTotal, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Resumen --}}
                @php $b = $this->breakdown; @endphp
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex justify-end">
                        <dl class="w-full sm:w-72 space-y-1.5 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.subtotal') }}</dt>
                                <dd class="text-gray-900 dark:text-white font-mono">{{ $currency }} {{ number_format($b['subtotal'], 2) }}</dd>
                            </div>
                            @if($b['discount'] > 0)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.discount') }}</dt>
                                <dd class="text-rose-600 dark:text-rose-400 font-mono">− {{ $currency }} {{ number_format($b['discount'], 2) }}</dd>
                            </div>
                            @endif
                            @if($b['tax'] > 0)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.tax') }}</dt>
                                <dd class="text-gray-900 dark:text-white font-mono">{{ $currency }} {{ number_format($b['tax'], 2) }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                <dt class="font-semibold text-gray-900 dark:text-white">{{ __('invoices.total') }}</dt>
                                <dd class="font-bold text-base text-gray-900 dark:text-white font-mono">{{ $currency }} {{ number_format($b['total'], 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-2 space-y-reverse sm:space-y-0">
                <a href="{{ route('app.invoices.index', ['clinic' => $currentClinic->slug]) }}"
                   class="inline-flex justify-center px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    {{ __('general.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
