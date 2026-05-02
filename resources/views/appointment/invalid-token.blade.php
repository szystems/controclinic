<x-public-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                {{-- Icon --}}
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-6">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('appointments_mail.invalid_token_title') }}
                </h1>

                <p class="text-gray-600">
                    {{ __('appointments_mail.invalid_token_message') }}
                </p>
            </div>
        </div>
    </div>
</x-public-layout>
