@props([
    'label'          => null,
    'name'           => 'phone',
    'codeName'       => 'phone_country_code',
    'value'          => '',
    'codeValue'      => '502',
    'defaultCode'    => '502',
    'required'       => false,
    'placeholder'    => '',
])

@php
    $countryCodes = [
        ['code' => '502',  'flag' => '🇬🇹'],
        ['code' => '503',  'flag' => '🇸🇻'],
        ['code' => '504',  'flag' => '🇭🇳'],
        ['code' => '505',  'flag' => '🇳🇮'],
        ['code' => '506',  'flag' => '🇨🇷'],
        ['code' => '507',  'flag' => '🇵🇦'],
        ['code' => '52',   'flag' => '🇲🇽'],
        ['code' => '1',    'flag' => '🇺🇸'],
        ['code' => '57',   'flag' => '🇨🇴'],
        ['code' => '51',   'flag' => '🇵🇪'],
        ['code' => '56',   'flag' => '🇨🇱'],
        ['code' => '54',   'flag' => '🇦🇷'],
        ['code' => '55',   'flag' => '🇧🇷'],
        ['code' => '58',   'flag' => '🇻🇪'],
        ['code' => '591',  'flag' => '🇧🇴'],
        ['code' => '595',  'flag' => '🇵🇾'],
        ['code' => '598',  'flag' => '🇺🇾'],
        ['code' => '593',  'flag' => '🇪🇨'],
        ['code' => '1809', 'flag' => '🇩🇴'],
        ['code' => '53',   'flag' => '🇨🇺'],
        ['code' => '34',   'flag' => '🇪🇸'],
        ['code' => '44',   'flag' => '🇬🇧'],
        ['code' => '33',   'flag' => '🇫🇷'],
        ['code' => '49',   'flag' => '🇩🇪'],
    ];
    $resolvedCode = $codeValue ?: $defaultCode;
@endphp

<div>
    @if($label)
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        {{ $label }}@if($required) <span class="text-red-500">*</span>@endif
    </label>
    @endif

    <div class="flex gap-2">
        {{-- Country code selector --}}
        <select wire:model="{{ $codeName }}"
                id="{{ $codeName }}"
                class="w-40 shrink-0 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error($codeName) border-red-500 @enderror">
            @foreach($countryCodes as $country)
                <option value="{{ $country['code'] }}" @selected($resolvedCode === $country['code'])>
                    {{ $country['flag'] }} {{ __('patients.phone_countries.'.$country['code']) }}
                </option>
            @endforeach
        </select>

        {{-- Local number input --}}
        <input wire:model="{{ $name }}"
               type="tel"
               id="{{ $name }}"
               placeholder="{{ $placeholder }}"
               @if($required) required @endif
               class="flex-1 min-w-0 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error($name) border-red-500 @enderror">
    </div>

    @error($codeName)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
