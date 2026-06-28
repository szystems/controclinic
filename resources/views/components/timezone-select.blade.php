@props([
    'id' => 'timezone',
    'wireModel' => 'timezone',
])

<select
    wire:model="{{ $wireModel }}"
    id="{{ $id }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}
>
    @foreach (config('timezones.groups', []) as $groupKey => $zones)
        <optgroup label="{{ __('timezones.groups.'.$groupKey) }}">
            @foreach ($zones as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
