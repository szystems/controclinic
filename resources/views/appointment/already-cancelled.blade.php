<x-public-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                {{-- Icon --}}
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('appointments_mail.already_cancelled_title') }}
                </h1>

                <p class="text-gray-600">
                    {{ __('appointments_mail.already_cancelled_message') }}
                </p>
            </div>
        </div>
    </div>
</x-public-layout>
