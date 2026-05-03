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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('invoices.invoice') }} #{{ $invoice->invoice_number }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ __('invoices.issued_at') }}: {{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y') }}
                        @if($invoice->due_at)
                            · {{ __('invoices.due_at') }}: {{ \Carbon\Carbon::parse($invoice->due_at)->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $invoice->status_color }}-100 text-{{ $invoice->status_color }}-800 dark:bg-{{ $invoice->status_color }}-900/30 dark:text-{{ $invoice->status_color }}-300">
                    {{ $invoice->status_label }}
                </span>
                @can('invoices.print')
                <a href="{{ route('app.invoices.pdf', ['clinic' => $currentClinic->slug, 'invoice' => $invoice->id]) }}"
                   target="_blank"
                   class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    PDF
                </a>
                @endcan
                @can('invoices.record_payment')
                @if(!$invoice->isCancelled() && !$invoice->isPaid())
                <button type="button" wire:click="openPaymentModal"
                        class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-green-700 focus:outline-none">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('invoices.record_payment') }}
                </button>
                @endif
                @endcan
                @can('invoices.edit')
                @if(!$invoice->isCancelled() && !$invoice->isPaid())
                @if($invoice->payments->isEmpty())
                <a href="{{ route('app.invoices.edit', ['clinic' => $currentClinic->slug, 'invoice' => $invoice->id]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('general.edit') }}
                </a>
                @endif
                <button type="button" wire:click="cancel"
                        wire:confirm="{{ __('invoices.confirm_cancel') }}"
                        class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-red-300 dark:border-red-600 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                    {{ __('invoices.mark_as_cancelled') }}
                </button>
                @endif
                @endcan
            </div>
        </div>

        {{-- Flash --}}
        @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Columna principal --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Datos del paciente / doctor --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('invoices.patient') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->patient->full_name ?? '—' }}</p>
                            @if($invoice->patient?->email)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->patient->email }}</p>
                            @endif
                        </div>
                        @if($invoice->doctor)
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('invoices.doctor') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->doctor->name }}</p>
                        </div>
                        @endif
                    </div>
                    @if($invoice->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('invoices.notes') }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                </div>

                {{-- Ítems --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.items') }}</h2>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.item_description') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('invoices.item_quantity') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('invoices.item_unit_price') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('invoices.item_total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($invoice->items->sortBy('order') as $item)
                            <tr>
                                <td class="px-6 py-3">
                                    <p class="text-sm text-gray-900 dark:text-white">{{ $item->description }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->type_label }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Historial de pagos --}}
                @if($invoice->payments->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('invoices.payments') }}</h2>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.payment_date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.payment_method') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.payment_reference') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('invoices.payment_amount') }}</th>
                                @can('invoices.record_payment')
                                @if(!$invoice->isCancelled())
                                <th class="px-4 py-3 w-16"></th>
                                @endif
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($invoice->payments->sortBy('paid_at') as $payment)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->paid_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->method_label }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $payment->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ number_format($payment->amount, 2) }}</td>
                                @can('invoices.record_payment')
                                @if(!$invoice->isCancelled())
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            wire:click="deletePayment('{{ $payment->id }}')"
                                            wire:confirm="{{ __('invoices.confirm_delete_payment') }}"
                                            class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors"
                                            title="{{ __('invoices.delete_payment') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                </td>
                                @endif
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Columna lateral — Totales --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('general.summary') }}</h2>
                    <dl class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.subtotal') }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ number_format($invoice->subtotal, 2) }}</dd>
                        </div>
                        @if($invoice->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.discount') }}</dt>
                            <dd class="text-red-600 dark:text-red-400">-{{ number_format($invoice->discount_amount, 2) }}</dd>
                        </div>
                        @endif
                        @if($invoice->tax_amount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.tax') }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ number_format($invoice->tax_amount, 2) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between text-base font-semibold border-t border-gray-100 dark:border-gray-700 pt-2 mt-2">
                            <dt class="text-gray-900 dark:text-white">{{ __('invoices.total') }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</dd>
                        </div>
                        @if($invoice->paid_amount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('invoices.paid') }}</dt>
                            <dd class="text-green-600 dark:text-green-400">{{ number_format($invoice->paid_amount, 2) }}</dd>
                        </div>
                        @endif
                        @if($invoice->balance > 0)
                        <div class="flex justify-between text-sm font-semibold text-red-600 dark:text-red-400">
                            <dt>{{ __('invoices.balance') }}</dt>
                            <dd>{{ number_format($invoice->balance, 2) }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de pago --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 flex items-center justify-center z-50" x-data @keydown.escape.window="$wire.closePaymentModal()">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('invoices.record_payment') }}</h3>
                <button type="button" wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.payment_amount') }}</label>
                    <input type="number" wire:model="pay_amount" step="0.01" min="0.01"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                    @error('pay_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.payment_method') }}</label>
                    <select wire:model="pay_method" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($paymentMethods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.payment_date') }}</label>
                    <input type="date" wire:model="pay_date"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                    @error('pay_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('invoices.payment_reference') }}</label>
                    <input type="text" wire:model="pay_reference"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" wire:click="closePaymentModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ __('general.cancel') }}
                    </button>
                    <button type="button" wire:click="recordPayment"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        {{ __('invoices.record_payment') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
