    @props([
    'id' => 'id_type',
    'wireModel' => 'id_type',
    'currentValue' => null,
])

@php
    $types = __('patients.id_types');
    $legacyValues = array_values(array_filter([
        is_string($currentValue) ? $currentValue : null,
    ]));
    $knownKeys = is_array($types) ? array_keys($types) : [];
@endphp

<select
    wire:model="{{ $wireModel }}"
    id="{{ $id }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm']) }}
>
    <option value="">{{ __('general.select') }}</option>
    @if (is_array($types))
        @foreach ($types as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    @endif
    @foreach ($legacyValues as $legacy)
        @if ($legacy && ! in_array($legacy, $knownKeys, true))
            <option value="{{ $legacy }}">{{ $legacy }}</option>
        @endif
    @endforeach
</select>
