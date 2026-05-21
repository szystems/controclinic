{{-- Partial reutilizable: formulario de items (repeater) --}}
{{-- Usado por create.blade.php y edit.blade.php --}}

<div class="space-y-3" id="prescription-items">
    @foreach($items as $i => $item)
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 p-4"
         wire:key="item-{{ $i }}">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                {{ __('prescriptions.medication') }} #{{ $i + 1 }}
            </span>
            @if(count($items) > 1)
            <button type="button" wire:click="removeItem({{ $i }})"
                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            {{-- Nombre del medicamento (requerido) --}}
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('prescriptions.medication_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.lazy="items.{{ $i }}.medication_name"
                       placeholder="{{ __('prescriptions.medication_name_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                @error("items.{$i}.medication_name") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Presentación --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.presentation') }}</label>
                <input type="text" wire:model.lazy="items.{{ $i }}.presentation"
                       placeholder="{{ __('prescriptions.presentation_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Dosis --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.dose') }}</label>
                <input type="text" wire:model.lazy="items.{{ $i }}.dose"
                       placeholder="{{ __('prescriptions.dose_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Frecuencia --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.frequency') }}</label>
                <input type="text" wire:model.lazy="items.{{ $i }}.frequency"
                       placeholder="{{ __('prescriptions.frequency_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Duración --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.duration') }}</label>
                <input type="text" wire:model.lazy="items.{{ $i }}.duration"
                       placeholder="{{ __('prescriptions.duration_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Vía de administración --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.route') }}</label>
                <select wire:model.lazy="items.{{ $i }}.route"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">—</option>
                    <option value="oral">{{ __('prescriptions.route_oral') }}</option>
                    <option value="topical">{{ __('prescriptions.route_topical') }}</option>
                    <option value="injectable">{{ __('prescriptions.route_injectable') }}</option>
                    <option value="inhalation">{{ __('prescriptions.route_inhalation') }}</option>
                    <option value="sublingual">{{ __('prescriptions.route_sublingual') }}</option>
                    <option value="ophthalmic">{{ __('prescriptions.route_ophthalmic') }}</option>
                    <option value="otic">{{ __('prescriptions.route_otic') }}</option>
                    <option value="rectal">{{ __('prescriptions.route_rectal') }}</option>
                    <option value="other">{{ __('prescriptions.route_other') }}</option>
                </select>
            </div>

            {{-- Cantidad a dispensar --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.quantity') }}</label>
                <input type="number" wire:model.lazy="items.{{ $i }}.quantity" min="1" max="999"
                       placeholder="—"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Instrucciones --}}
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.instructions') }}</label>
                <input type="text" wire:model.lazy="items.{{ $i }}.instructions"
                       placeholder="{{ __('prescriptions.instructions_placeholder') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Sustancia controlada --}}
            <div class="flex items-center gap-2 pt-5">
                <input type="checkbox" wire:model="items.{{ $i }}.is_controlled" id="controlled-{{ $i }}"
                       class="rounded border-gray-300 text-red-600 focus:ring-red-500"/>
                <label for="controlled-{{ $i }}" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                    {{ __('prescriptions.is_controlled') }}
                </label>
            </div>
        </div>
    </div>
    @endforeach

    <button type="button" wire:click="addItem"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('prescriptions.add_medication') }}
    </button>
</div>
