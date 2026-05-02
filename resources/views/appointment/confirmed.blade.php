<x-public-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                {{-- Icon --}}
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-6">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('appointments_mail.confirm_page_title') }}
                </h1>

                <p class="text-gray-600 mb-6">
                    {{ __('appointments_mail.confirm_page_message') }}
                </p>

                {{-- Appointment details --}}
                <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 mb-6">
                    @php
                        $date = $appointment->appointment_date->translatedFormat('l, d F Y');
                        $time = $appointment->start_time
                            ? \Carbon\Carbon::parse($appointment->start_time)->format('H:i')
                            : null;
                    @endphp

                    @if($appointment->clinic)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ __('appointments_mail.clinic_info') }}</span>
                            <span class="font-medium text-gray-800">{{ $appointment->clinic->name }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('appointments_mail.label_date') }}</span>
                        <span class="font-medium text-gray-800">{{ $date }}</span>
                    </div>

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

                <p class="text-sm text-gray-500">
                    {{ __('appointments_mail.confirm_page_note') }}
                </p>
            </div>
        </div>
    </div>
</x-public-layout>
