<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-help-banner module="invoices" />
        {{-- Header --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('invoices.invoices') }}
                </h1>
            </div>
            @can('invoices.create')
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('app.invoices.create', ['clinic' => $currentClinic->slug]) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('invoices.new_invoice') }}
                </a>
            </div>
            @endcan
        </div>

        {{-- Flash --}}
        @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2">
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('general.search') }}…"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div>
                    <select wire:model.live="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('invoices.all_statuses') }}</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}">{{ __('invoices.status_' . $s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" wire:model.live="dateFrom"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div>
                    <input type="date" wire:model.live="dateTo"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                {{-- Vencidas --}}
                <div>
                    <select wire:model.live="filterOverdue" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('invoices.all_due') }}</option>
                        <option value="yes">{{ __('invoices.only_overdue') }}</option>
                    </select>
                </div>
                {{-- Método de pago --}}
                <div>
                    <select wire:model.live="filterPaymentMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('invoices.all_payment_methods') }}</option>
                        <option value="cash">{{ __('invoices.method_cash') }}</option>
                        <option value="card">{{ __('invoices.method_card') }}</option>
                        <option value="transfer">{{ __('invoices.method_transfer') }}</option>
                        <option value="insurance">{{ __('invoices.method_insurance') }}</option>
                        <option value="other">{{ __('invoices.method_other') }}</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($invoices->isEmpty())
            <x-empty-state
                icon="invoice"
                :title="__('invoices.no_invoices')"
                :description="__('invoices.no_invoices_desc')"
                :bullets="[__('invoices.empty_state_bullet_1'), __('invoices.empty_state_bullet_2'), __('invoices.empty_state_bullet_3')]"
                :cta-text="__('invoices.new_invoice')"
                :cta-route="route('app.invoices.create', ['clinic' => $currentClinic->slug])"
                cta-permission="invoices.create"
            />
            @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.invoice_number') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.patient') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.issued_at') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.total') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.balance') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.days_overdue') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.last_payment_method') }}</th>
                        <th class="relative px-6 py-3"><span class="sr-only">{{ __('general.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($invoices as $invoice)
                    <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" @click="window.location.href='{{ route('app.invoices.show', ['clinic' => $currentClinic->slug, 'invoice' => $invoice->id]) }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-semibold text-gray-900 dark:text-white">
                            {{ $invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                            {{ $invoice->patient->full_name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white font-medium">
                            {{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $invoice->balance > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ number_format($invoice->balance, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $invoice->status_color }}-100 text-{{ $invoice->status_color }}-800 dark:bg-{{ $invoice->status_color }}-900/30 dark:text-{{ $invoice->status_color }}-300">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @php
                                $isOverdue = in_array($invoice->status, [\App\Models\Invoice::STATUS_PENDING, \App\Models\Invoice::STATUS_PARTIAL])
                                    && $invoice->due_at
                                    && \Carbon\Carbon::parse($invoice->due_at)->isPast();
                                $daysOverdue = $isOverdue ? (int) \Carbon\Carbon::parse($invoice->due_at)->diffInDays(now()) : 0;
                            @endphp
                            @if($isOverdue)
                                <span class="font-semibold text-red-600 dark:text-red-400">{{ $daysOverdue }}d</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            @if($invoice->payments->isNotEmpty())
                                {{ __('invoices.method_' . $invoice->payments->first()->method) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                            <a href="{{ route('app.invoices.show', ['clinic' => $currentClinic->slug, 'invoice' => $invoice->id]) }}"
                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                {{ __('general.view') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
