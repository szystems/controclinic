<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('app.settings.index', ['clinic' => $currentClinic->slug]) }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('catalog.title') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ __('catalog.subtitle') }}</p>
                </div>
            </div>
            @can('settings.manage')
            <button type="button" wire:click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-primary/90 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('catalog.add_item') }}
            </button>
            @endcan
        </div>

        {{-- Flash --}}
        @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
        @endif
        @if(session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ session('error') }}
        </div>
        @endif

        {{-- Filtros --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('catalog.search_placeholder') }}"
                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 focus:border-primary"/>
            </div>
            <select wire:model.live="filterType"
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50">
                <option value="">{{ __('catalog.all_types') }}</option>
                <option value="service">{{ __('catalog.service') }}</option>
                <option value="product">{{ __('catalog.product') }}</option>
            </select>
        </div>

        {{-- Tabla --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($items->count() > 0)
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('catalog.name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('catalog.type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">{{ __('catalog.sku') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('catalog.price') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('catalog.status') }}</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($items as $item)
                    <tr class="{{ !$item->is_active ? 'opacity-50' : '' }}">
                        <td class="px-6 py-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item->type === 'service' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' }}">
                                {{ __('catalog.'.$item->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono hidden sm:table-cell">{{ $item->sku ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                            {{ number_format($item->default_price, 2) }}
                            @if($item->tax_rate_override !== null)
                            <span class="text-xs text-gray-400 font-normal ml-1">(+{{ $item->tax_rate_override }}%)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @can('settings.manage')
                            <button type="button" wire:click="toggleActive('{{ $item->id }}')"
                                    class="text-xs {{ $item->is_active ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600' }} transition-colors font-medium">
                                {{ $item->is_active ? __('catalog.active') : __('catalog.inactive') }}
                            </button>
                            @else
                            <span class="text-xs {{ $item->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $item->is_active ? __('catalog.active') : __('catalog.inactive') }}
                            </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                @can('settings.manage')
                                <button type="button" wire:click="openEdit('{{ $item->id }}')"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                                        title="{{ __('general.edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        wire:click="delete('{{ $item->id }}')"
                                        wire:confirm="{{ __('catalog.confirm_delete') }}"
                                        class="text-red-400 hover:text-red-600 transition-colors"
                                        title="{{ __('general.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $items->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('catalog.empty') }}</p>
                @can('settings.manage')
                <button type="button" wire:click="openCreate"
                        class="mt-3 text-sm text-primary hover:underline">
                    {{ __('catalog.add_first') }}
                </button>
                @endcan
            </div>
            @endif
        </div>

    </div>

    {{-- Modal crear/editar --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
         x-data x-on:keydown.escape.window="$wire.closeModal()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $editingId ? __('catalog.edit_item') : __('catalog.new_item') }}
                </h2>
                <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="px-6 py-5 space-y-4">
                {{-- Nombre + Tipo --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('catalog.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="name" maxlength="120"
                               class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50 focus:border-primary @error('name') border-red-400 @enderror"/>
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('catalog.type') }} *</label>
                        <select wire:model="type"
                                class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50">
                            <option value="service">{{ __('catalog.service') }}</option>
                            <option value="product">{{ __('catalog.product') }}</option>
                        </select>
                    </div>
                </div>

                {{-- SKU + Unidad --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('catalog.sku') }}</label>
                        <input type="text" wire:model="sku" maxlength="60"
                               placeholder="Opcional"
                               class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('catalog.unit') }}</label>
                        <select wire:model="unit"
                                class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50">
                            <option value="unit">{{ __('catalog.unit_unit') }}</option>
                            <option value="session">{{ __('catalog.unit_session') }}</option>
                            <option value="hour">{{ __('catalog.unit_hour') }}</option>
                            <option value="mg">mg</option>
                            <option value="ml">ml</option>
                            <option value="g">g</option>
                            <option value="box">{{ __('catalog.unit_box') }}</option>
                        </select>
                    </div>
                </div>

                {{-- Precio + Impuesto --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('catalog.default_price') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="default_price" step="0.01" min="0"
                               class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50 @error('default_price') border-red-400 @enderror"/>
                        @error('default_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('catalog.tax_rate_override') }}
                        </label>
                        <input type="number" wire:model="tax_rate_override" step="0.01" min="0" max="100"
                               placeholder="{{ __('catalog.tax_from_clinic') }}"
                               class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50"/>
                        <p class="mt-1 text-xs text-gray-400">{{ __('catalog.tax_override_hint') }}</p>
                    </div>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('catalog.description') }}</label>
                    <textarea wire:model="description" rows="2" maxlength="500"
                              class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary/50 resize-none"></textarea>
                </div>

                {{-- Activo --}}
                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_active" id="catalog_active"
                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary/50"/>
                    <label for="catalog_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('catalog.mark_active') }}</label>
                </div>

                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        {{ __('general.cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition">
                        {{ __('general.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
