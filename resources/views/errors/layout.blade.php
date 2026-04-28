@php($code = $code ?? 500)
@php($title = $title ?? __('errors.title_'.$code))
@php($message = $message ?? __('errors.message_'.$code))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — ControClinic</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex items-center">
    <div class="max-w-2xl mx-auto px-6 py-12 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-indigo-100 mb-6">
            <span class="text-4xl font-extrabold text-indigo-600">{{ $code }}</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">{{ $title }}</h1>
        <p class="text-lg text-gray-600 mb-8">{{ $message }}</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/') }}"
               class="inline-flex items-center px-5 py-2.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                {{ __('errors.go_home') }}
            </a>
            @auth
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center px-5 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                    {{ __('errors.go_back') }}
                </a>
            @endauth
        </div>
        <p class="mt-10 text-sm text-gray-400">ControClinic — SaaS para clínicas</p>
    </div>
</body>
</html>
