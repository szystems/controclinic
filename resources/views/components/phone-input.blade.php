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
        ['code' => '502',  'flag' => '🇬🇹', 'label' => '+502 Guatemala'],
        ['code' => '503',  'flag' => '🇸🇻', 'label' => '+503 El Salvador'],
        ['code' => '504',  'flag' => '🇭🇳', 'label' => '+504 Honduras'],
        ['code' => '505',  'flag' => '🇳🇮', 'label' => '+505 Nicaragua'],
        ['code' => '506',  'flag' => '🇨🇷', 'label' => '+506 Costa Rica'],
        ['code' => '507',  'flag' => '🇵🇦', 'label' => '+507 Panamá'],
        ['code' => '52',   'flag' => '🇲🇽', 'label' => '+52 México'],
        ['code' => '1',    'flag' => '🇺🇸', 'label' => '+1 EE.UU./Canadá'],
        ['code' => '57',   'flag' => '🇨🇴', 'label' => '+57 Colombia'],
        ['code' => '51',   'flag' => '🇵🇪', 'label' => '+51 Perú'],
        ['code' => '56',   'flag' => '🇨🇱', 'label' => '+56 Chile'],
        ['code' => '54',   'flag' => '🇦🇷', 'label' => '+54 Argentina'],
        ['code' => '55',   'flag' => '🇧🇷', 'label' => '+55 Brasil'],
        ['code' => '58',   'flag' => '🇻🇪', 'label' => '+58 Venezuela'],
        ['code' => '591',  'flag' => '🇧🇴', 'label' => '+591 Bolivia'],
        ['code' => '595',  'flag' => '🇵🇾', 'label' => '+595 Paraguay'],
        ['code' => '598',  'flag' => '🇺🇾', 'label' => '+598 Uruguay'],
        ['code' => '593',  'flag' => '🇪🇨', 'label' => '+593 Ecuador'],
        ['code' => '1809', 'flag' => '🇩🇴', 'label' => '+1809 Rep. Dominicana'],
        ['code' => '53',   'flag' => '🇨🇺', 'label' => '+53 Cuba'],
        ['code' => '34',   'flag' => '🇪🇸', 'label' => '+34 España'],
        ['code' => '44',   'flag' => '🇬🇧', 'label' => '+44 Reino Unido'],
        ['code' => '33',   'flag' => '🇫🇷', 'label' => '+33 Francia'],
        ['code' => '49',   'flag' => '🇩🇪', 'label' => '+49 Alemania'],
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
                    {{ $country['flag'] }} {{ $country['label'] }}
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
