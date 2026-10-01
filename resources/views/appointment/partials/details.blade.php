@php
    $date = $appointment->appointment_date?->translatedFormat('l, d F Y');
    $time = $appointment->start_time
        ? \Carbon\Carbon::parse($appointment->start_time)->format('H:i')
        : null;
@endphp

<div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 mb-6">
    @if($appointment->clinic)
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">{{ __('appointments_mail.clinic_info') }}</span>
            <span class="font-medium text-gray-800">{{ $appointment->clinic->name }}</span>
        </div>
    @endif

    @if($date)
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">{{ __('appointments_mail.label_date') }}</span>
            <span class="font-medium text-gray-800">{{ $date }}</span>
        </div>
    @endif

    @if($time)
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">{{ __('appointments_mail.label_time') }}</span>
            <span class="font-medium text-gray-800">{{ $time }}</span>
        </div>
    @endif

    @if($appointment->doctor)
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">{{ __('appointments_mail.label_doctor') }}</span>
            <span class="font-medium text-gray-800">{{ $appointment->doctor->name }}</span>
        </div>
    @endif
</div>
