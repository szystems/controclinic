<x-public-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('appointments.link_unavailable_title') }}
                </h1>

                <p class="text-gray-600 mb-6">
                    {{ $message }}
                </p>

                @include('appointment.partials.details', ['appointment' => $appointment])
            </div>
        </div>
    </div>
</x-public-layout>
