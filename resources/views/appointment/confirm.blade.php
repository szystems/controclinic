<x-public-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('appointments.link_confirm_title') }}
                </h1>

                <p class="text-gray-600 mb-6">
                    {{ __('appointments.link_confirm_message') }}
                </p>

                @include('appointment.partials.details', ['appointment' => $appointment])

                <form method="POST" action="{{ route('appointment.confirm.store', $appointment->confirmation_token) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700">
                        {{ __('appointments.link_confirm_button') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-public-layout>
