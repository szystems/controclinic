<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('app.invoices.index', ['clinic' => $currentClinic->slug]) }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('invoices.new_invoice') }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('invoices.currency') }}: <span class="font-mono font-semibold">{{ $currency }}</span>
                    </p>
                </div>
            </div>
            @if($appointmentId)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('invoices.from_appointment') }}
                </span>
            @endif
        </div>

        <form wire:submit="save" class="space-y-6">

            {{-- ── Encabezado ── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.invoice_details') }}</h2>

                {{-- Paciente --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ __('invoices.patient') }} <span class="text-red-500">*</span>
                    </label>

                    @if($patient_id && $patientName)
                        {{-- Chip paciente seleccionado --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 shrink-0 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($patientName, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $patientName }}</p>
                                    @if($patientEmail || $patientPhone)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ implode(' · ', array_filter([$patientEmail ?? '', $patientPhone ?? ''])) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @if(!$appointmentId)
                                <button type="button" wire:click="clearPatient"
                                        class="shrink-0 text-gray-400 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @else
                        {{-- Buscador --}}
                        <div class="relative" x-data="{ open: false }" @keydown.escape="open = false" @click.outside="open = false">
                            <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                                <span class="flex items-center px-3 bg-gray-50 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       wire:model.live.debounce.300ms="patientSearch"
                                       @focus="open = true"
                                       placeholder="{{ __('patients.search_placeholder') }}"
                                       class="flex-1 min-w-0 border-0 bg-white dark:bg-gray-700 dark:text-white text-sm focus:ring-0 py-2 px-3"/>
                            </div>
                            @if(strlen($patientSearch) >= 2)
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">
                                    @forelse($patients as $patient)
                                        <button type="button"
                                                wire:click="selectPatient('{{ $patient['id'] }}', @js($patient['full_name']), @js($patient['email'] ?? ''), @js($patient['phone'] ?? ''))"
                                                @click="open = false"
                                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient['full_name'] }}</p>
                                            @if(!empty($patient['email']) || !empty($patient['phone']))
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ implode(' · ', array_filter([$patient['email'] ?? '', $patient['phone'] ?? ''])) }}
                                                </p>
                                            @endif
                                        </button>
                                    @empty
                                        <p class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ __('general.no_results') }}</p>
                                    @endforelse
                                </div>
                            @else
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('invoices.patient_search_hint') }}</p>
                            @endif
                        </div>
                    @endif
                    @error('patient_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Doctor --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.doctor') }}</label>
                    @if($doctors->isEmpty())
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('invoices.no_doctors') }}</p>
                    @else
                        <select wire:model="doctor_id"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— {{ __('general.optional') }} —</option>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('invoices.issued_at') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" wire:model="issued_at"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('issued_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.due_at') }}</label>
                        <input type="date" wire:model="due_at"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('due_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('invoices.notes') }}</label>
                    <textarea wire:model="notes" rows="2"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ── Ítems ── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Header de la sección --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.items') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($items) }} {{ __('invoices.items') }}</p>
                        @can('settings.edit')
                            <a href="{{ route('app.settings.catalog', ['clinic' => $currentClinic->slug]) }}"
                               class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-0.5 inline-block">
                                {{ __('invoices.manage_catalog_link') }}
                            </a>
                        @endcan
                    </div>
                    <button type="button" wire:click="addItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('invoices.add_item') }}
                    </button>
                </div>

                @error('items') <p class="px-6 py-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($items as $i => $item)
                        @php
                            $qty   = (float) ($item['quantity'] ?? 0);
                            $price = (float) ($item['unit_price'] ?? 0);
                            $disc  = (float) ($item['discount_amount'] ?? 0);
                            $rate  = (float) ($item['tax_rate'] ?? 0);
                            $base  = $qty * $price;
                            $net   = max($base - $disc, 0);
                            $lineTotal = round($net + ($net * $rate / 100), 2);
                        @endphp
                        <div class="px-6 py-5 space-y-4" wire:key="item-{{ $i }}">

                            {{-- Cabecera de la línea --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $i + 1 }}</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('invoices.item') }} #{{ $i + 1 }}</span>
                                </div>
                                @if(count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $i }})"
                                            class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-800 font-medium transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                        {{ __('invoices.remove_item') }}
                                    </button>
                                @endif
                            </div>

                            {{-- Tipo --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_type') }}</label>
                                <select wire:model="items.{{ $i }}.type"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($itemTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Descripción con autocomplete del catálogo --}}
                            <div x-data="{
                                    open: false,
                                    results: [],
                                    loading: false,
                                    search(term) {
                                        if (term.length < 1) { this.results = []; this.open = false; return; }
                                        this.loading = true;
                                        $wire.searchCatalog(term).then(r => {
                                            this.results = r;
                                            this.open = r.length > 0;
                                            this.loading = false;
                                        });
                                    },
                                    pick(item, index) {
                                        $wire.fillItemFromCatalog(index, item.id);
                                        this.open = false;
                                        this.results = [];
                                    }
                                }" class="relative">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ __('invoices.item_description') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model="items.{{ $i }}.description"
                                           x-on:input="search($event.target.value)"
                                           x-on:blur="setTimeout(() => open = false, 200)"
                                           placeholder="{{ __('invoices.item_description') }}"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 pr-8"/>
                                    <span x-show="loading" class="absolute right-2 top-2.5">
                                        <svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                    </span>
                                </div>
                                <ul x-show="open" x-cloak
                                    class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto text-sm">
                                    <template x-for="res in results" :key="res.id">
                                        <li x-on:mousedown.prevent="pick(res, {{ $i }})"
                                            class="flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white" x-text="res.name"></span>
                                                <span class="ml-2 text-xs text-gray-400" x-text="res.unit !== 'unit' ? res.unit : ''"></span>
                                            </div>
                                            <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300" x-text="res.price.toFixed(2)"></span>
                                        </li>
                                    </template>
                                </ul>
                                @error("items.{$i}.description") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Cantidad --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_quantity') }}</label>
                                <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.quantity"
                                       step="0.01" min="0.01"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                            </div>

                            {{-- Precio unitario --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_unit_price') }}</label>
                                <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                                    <span class="flex items-center px-3 bg-gray-50 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 text-xs font-mono text-gray-500 dark:text-gray-400 whitespace-nowrap select-none">{{ $currency }}</span>
                                    <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.unit_price"
                                           step="0.01" min="0"
                                           class="flex-1 min-w-0 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-0 py-2 px-3"/>
                                </div>
                            </div>

                            {{-- Descuento --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_discount') }}</label>
                                <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                                    <span class="flex items-center px-3 bg-gray-50 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 text-xs font-mono text-gray-500 dark:text-gray-400 whitespace-nowrap select-none">{{ $currency }}</span>
                                    <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.discount_amount"
                                           step="0.01" min="0"
                                           class="flex-1 min-w-0 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-0 py-2 px-3"/>
                                </div>
                            </div>

                            {{-- Impuesto --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('invoices.item_tax_rate') }}</label>
                                <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                                    <input type="number" wire:model.live.debounce.500ms="items.{{ $i }}.tax_rate"
                                           step="0.01" min="0" max="100"
                                           class="flex-1 min-w-0 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-0 py-2 px-3"/>
                                    <span class="flex items-center px-3 bg-gray-50 dark:bg-gray-700 border-l border-gray-300 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-400 select-none">%</span>
                                </div>
                            </div>

                            {{-- Total de línea --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('invoices.item_total') }}</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white tabular-nums">{{ $currency }} {{ number_format($lineTotal, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Resumen de totales --}}
                @php $b = $this->breakdown; @endphp
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700">
                    <dl class="ml-auto w-full sm:w-72 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.subtotal') }}</dt>
                            <dd class="tabular-nums text-gray-900 dark:text-white">{{ $currency }} {{ number_format($b['subtotal'], 2) }}</dd>
                        </div>
                        @if($b['discount'] > 0)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.discount') }}</dt>
                                <dd class="tabular-nums text-rose-600 dark:text-rose-400">− {{ $currency }} {{ number_format($b['discount'], 2) }}</dd>
                            </div>
                        @endif
                        @if($b['tax'] > 0)
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.tax') }}</dt>
                                <dd class="tabular-nums text-gray-900 dark:text-white">{{ $currency }} {{ number_format($b['tax'], 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                            <dt class="font-semibold text-gray-900 dark:text-white">{{ __('invoices.total') }}</dt>
                            <dd class="tabular-nums font-bold text-base text-gray-900 dark:text-white">{{ $currency }} {{ number_format($b['total'], 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- ── Botones ── --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <a href="{{ route('app.invoices.index', ['clinic' => $currentClinic->slug]) }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-colors">
                    <svg wire:loading wire:target="save"
                         class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    {{ __('general.save') }}
                </button>
            </div>

        </form>
    </div>

    {{-- ── Modal: Sugerir guardar ítems al catálogo ── --}}
    @if($showCatalogSuggestion)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-data="{
             selected: @js(array_fill(0, count($freeItemSuggestions), true)),
             indexes: @js(array_column($freeItemSuggestions, 'index')),
             get hasSelected() { return this.selected.some(v => v); },
             submit() {
                 const toSave = this.indexes.filter((_, i) => this.selected[i]);
                 $wire.saveItemsToCatalog(toSave);
             }
         }">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex items-start gap-3 mb-3">
                <div class="shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('invoices.suggest_catalog_title') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ __('invoices.suggest_catalog_desc') }}</p>
                </div>
            </div>

            <div class="space-y-2 my-4">
                @foreach($freeItemSuggestions as $k => $suggestion)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <input type="checkbox" x-model="selected[{{ $k }}]"
                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"/>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $suggestion['description'] }}</p>
                        </div>
                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400 shrink-0">{{ number_format($suggestion['unit_price'], 2) }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                <button type="button" wire:click="skipCatalogSuggestion"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    {{ __('invoices.suggest_catalog_skip') }}
                </button>
                <button type="button" x-on:click="submit" x-bind:disabled="!hasSelected"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('invoices.suggest_catalog_save') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
