@php
    $settings = $clinic->settings ?? [];
    $hours = [
        'days' => $settings['working_days'] ?? [1,2,3,4,5],
        'start' => $settings['working_hours_start'] ?? '08:00',
        'end' => $settings['working_hours_end'] ?? '18:00',
    ];
    $description    = $settings['description'] ?? null;
    $publicDesc     = $clinic->public_description;
    $coverUrl       = $clinic->public_cover_image_url
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($clinic->public_cover_image_url)
        : null;
    $services       = $clinic->public_services ?? [];
    $showDoctors    = (bool) ($clinic->public_show_doctors ?? true);
    $branding       = $clinic->branding ?? [];
    $logoPath       = $branding['logo'] ?? null;
    $logoUrl        = $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
    $dayNames       = [0 => 'Dom', 1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb'];
    $workingDayList = collect($hours['days'])->map(fn($d) => $dayNames[$d] ?? '')->implode(', ');
@endphp

<div>
    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- HERO                                                   --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-xl shadow-sm mb-8
        {{ $coverUrl ? '' : 'bg-clinic-primary' }}"
         style="{{ $coverUrl ? '' : 'background: linear-gradient(135deg, var(--clinic-primary) 0%, var(--clinic-secondary, #6366f1) 100%)' }}">
        @if($coverUrl)
            <div class="absolute inset-0">
                <img src="{{ $coverUrl }}" alt="{{ $clinic->name }}" class="w-full h-full object-cover">
                <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.65) 100%)"></div>
            </div>
        @endif
        <div class="relative px-8 py-12 text-center text-white">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $clinic->name }}" class="h-16 w-auto mx-auto mb-4 rounded-lg shadow object-contain bg-white/10 p-1">
            @endif
            <h1 class="text-3xl sm:text-4xl font-bold leading-tight drop-shadow">{{ $clinic->name }}</h1>
            @if($description)
                <p class="mt-3 text-white/85 max-w-xl mx-auto text-base drop-shadow">{{ $description }}</p>
            @endif
            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-white/90">
                @if($clinic->phone)
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $clinic->phone }}
                    </span>
                @endif
                @if($workingDayList)
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $workingDayList }} · {{ $hours['start'] }}–{{ $hours['end'] }}
                    </span>
                @endif
                @if($clinic->city)
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $clinic->city }}
                    </span>
                @endif
            </div>
            @if(! $portalDisabled && $this->onlineBookingEnabled)
                <a href="#booking" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-white text-clinic-primary font-semibold rounded-full shadow hover:shadow-md transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('booking.book_appointment') }}
                </a>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SOBRE NOSOTROS                                         --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if($publicDesc)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('booking.about_us') }}
        </h2>
        <p class="text-gray-600 leading-relaxed whitespace-pre-line text-sm sm:text-base">{{ $publicDesc }}</p>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SERVICIOS                                              --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if(count($services) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            {{ __('booking.our_services') }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($services as $service)
                @if(! empty($service['title']))
                <div class="flex gap-3 p-4 rounded-lg bg-gray-50 border border-gray-100">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(var(--clinic-primary-rgb,79,70,229),0.1)">
                        <svg class="w-4 h-4 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-medium text-sm text-gray-900">{{ $service['title'] }}</p>
                        @if(! empty($service['description']))
                            <p class="text-xs text-gray-500 mt-0.5">{{ $service['description'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- EQUIPO MÉDICO                                          --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if($showDoctors)
    @php
        $doctors = $clinic->users()
            ->where(function ($q) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['owner', 'doctor']))
                  ->orWhereIn('role', ['owner', 'doctor']);
            })
            ->where('is_active', true)
            ->get();
    @endphp
    @if($doctors->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ __('booking.our_team') }}
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($doctors as $doctor)
            <div class="text-center p-4 rounded-lg bg-gray-50 border border-gray-100">
                <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center text-white text-lg font-bold"
                     style="background: linear-gradient(135deg, var(--clinic-primary) 0%, var(--clinic-secondary,#6366f1) 100%)">
                    {{ mb_strtoupper(mb_substr($doctor->name, 0, 1)) }}
                </div>
                <p class="text-sm font-medium text-gray-900 leading-tight">{{ $doctor->name }}</p>
                @php $role = $doctor->getRoleNames()->first(); @endphp
                @if($role)
                    <p class="text-xs text-gray-500 mt-0.5 capitalize">{{ __('roles.'.$role, ['default' => $role]) }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- BOOKING WIZARD (existente — sin cambios)               --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div id="booking">
        @if(! $portalDisabled && $this->onlineBookingEnabled && $step < 4)
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--clinic-primary) 0%, var(--clinic-secondary,#6366f1) 100%)">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('booking.book_appointment') }}</h2>
        </div>
        @endif

    {{-- Account in read-only / billing-only — booking unavailable (ADR-008) --}}
    @if($portalDisabled)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-amber-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('booking.booking_unavailable') }}</h2>
            <p class="text-gray-600">{{ __('booking.booking_unavailable_body') }}</p>
            @if($clinic->phone)
                <p class="text-gray-600 mt-2">{{ __('booking.phone') }}: <a href="tel:{{ $clinic->phone }}" class="text-clinic-primary font-medium">{{ $clinic->phone }}</a></p>
            @endif
        </div>

    {{-- Booking disabled --}}
    @elseif(! $this->onlineBookingEnabled)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-amber-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('booking.booking_disabled') }}</h2>
            @if($clinic->phone)
                <p class="text-gray-600">{{ __('booking.phone') }}: <a href="tel:{{ $clinic->phone }}" class="text-clinic-primary font-medium">{{ $clinic->phone }}</a></p>
            @endif
        </div>

    {{-- Confirmation screen --}}
    @elseif($step === 4 && $appointmentId)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 max-w-2xl mx-auto">
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center bg-clinic-primary-soft mb-4">
                    <svg class="w-8 h-8 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('booking.booking_confirmed') }}</h2>
                <p class="text-gray-600">
                    {{ $this->requiresConfirmation ? __('booking.booking_pending_confirmation') : __('booking.booking_auto_confirmed') }}
                </p>
            </div>

            <div class="border-t border-gray-200 pt-6 space-y-3">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('booking.appointment_details') }}</h3>
                @php
                    $selectedDoctor = $this->doctors->firstWhere('id', $doctor_id);
                @endphp
                @if($selectedDoctor)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('booking.appointment_with') }}</span>
                        <span class="font-medium text-gray-900">{{ $selectedDoctor->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('booking.select_date') }}</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('booking.select_time') }}</span>
                    <span class="font-medium text-gray-900">{{ $selectedTime }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('booking.appointment_at') }}</span>
                    <span class="font-medium text-gray-900 text-right">{{ $clinic->name }}</span>
                </div>
                @if($clinic->address)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('booking.address') }}</span>
                        <span class="text-gray-700 text-right">{{ $clinic->address }}</span>
                    </div>
                @endif
            </div>

            <div class="bg-clinic-primary-soft rounded-lg p-4 mt-6 text-center">
                <p class="text-xs uppercase tracking-wide text-gray-600 mb-1">{{ __('booking.reference_number') }}</p>
                <p class="font-mono text-lg font-bold text-clinic-primary">{{ $appointmentReference }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('booking.save_reference') }}</p>
            </div>

            <div class="mt-6 text-center">
                <button type="button" wire:click="startOver" class="text-sm font-medium text-clinic-primary hover:underline">
                    {{ __('booking.book_again') }}
                </button>
            </div>
        </div>

    {{-- Wizard --}}
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sidebar: clinic info --}}
            <aside class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ __('booking.contact') }}</h3>
                    @if($clinic->address)
                        <div class="flex items-start space-x-3 mb-3 text-sm text-gray-600">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $clinic->address }}{{ $clinic->city ? ', '.$clinic->city : '' }}</span>
                        </div>
                    @endif
                    @if($clinic->phone)
                        <div class="flex items-center space-x-3 mb-3 text-sm text-gray-600">
                            <svg class="w-4 h-4 flex-shrink-0 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:{{ $clinic->phone }}" class="hover:text-clinic-primary">{{ $clinic->phone }}</a>
                        </div>
                    @endif
                    @if($clinic->email)
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-4 h-4 flex-shrink-0 text-clinic-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:{{ $clinic->email }}" class="hover:text-clinic-primary">{{ $clinic->email }}</a>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ __('booking.opening_hours') }}</h3>
                    <ul class="space-y-1 text-sm">
                        @foreach([1,2,3,4,5,6,0] as $dayIdx)
                            @php $isOpen = in_array($dayIdx, $hours['days']); @endphp
                            <li class="flex justify-between {{ $isOpen ? 'text-gray-700' : 'text-gray-400' }}">
                                <span>{{ __('booking.days.'.$dayIdx) }}</span>
                                <span>{{ $isOpen ? $hours['start'].' - '.$hours['end'] : __('booking.closed') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if($description)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ __('booking.about_clinic') }}</h3>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $description }}</p>
                    </div>
                @endif
            </aside>

            {{-- Main wizard --}}
            <section class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                    {{-- Stepper --}}
                    <div class="border-b border-gray-200 px-6 py-4">
                        <p class="text-xs text-gray-500 mb-2">{{ __('booking.step', ['current' => $step, 'total' => $maxStep]) }}</p>
                        <div class="flex items-center space-x-2">
                            @foreach([1 => __('booking.step_doctor'), 2 => __('booking.step_datetime'), 3 => __('booking.step_details')] as $idx => $label)
                                <div class="flex items-center {{ $idx < $maxStep ? 'flex-1' : '' }}">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold
                                            {{ $step >= $idx ? 'btn-clinic-primary' : 'bg-gray-200 text-gray-500' }}">
                                            {{ $idx }}
                                        </div>
                                        <span class="text-sm font-medium hidden sm:inline {{ $step >= $idx ? 'text-gray-900' : 'text-gray-400' }}">{{ $label }}</span>
                                    </div>
                                    @if($idx < $maxStep)
                                        <div class="flex-1 h-0.5 mx-2 {{ $step > $idx ? 'bg-clinic-primary' : 'bg-gray-200' }}" style="background-color: {{ $step > $idx ? ($clinic->branding['primary_color'] ?? '#4f46e5') : '' }}"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-6">
                        @error('submit')
                            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">{{ $message }}</div>
                        @enderror

                        {{-- STEP 1: Doctor --}}
                        @if($step === 1)
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.select_doctor') }}</h2>

                            @if($this->doctors->isEmpty())
                                <p class="text-sm text-gray-500 text-center py-8">{{ __('booking.no_doctors') }}</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($this->doctors as $doctor)
                                        <button type="button" wire:click="selectDoctor('{{ $doctor->id }}')"
                                                class="w-full text-left p-4 border rounded-lg transition flex items-center space-x-4
                                                       {{ $doctor_id === (string)$doctor->id ? 'border-clinic-primary bg-clinic-primary-soft' : 'border-gray-200 hover:bg-gray-50' }}">
                                            <div class="flex-shrink-0">
                                                @if($doctor->avatar)
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($doctor->avatar) }}" alt="" class="w-12 h-12 rounded-full object-cover">
                                                @else
                                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold" style="background-color: {{ $clinic->branding['primary_color'] ?? '#4f46e5' }}">
                                                        {{ $doctor->initials }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 truncate">{{ $doctor->name }}</p>
                                                @if(! empty($doctor->specialties))
                                                    <p class="text-sm text-gray-500 truncate">{{ implode(' · ', (array) $doctor->specialties) }}</p>
                                                @endif
                                                @if($doctor->bio)
                                                    <p class="text-xs text-gray-400 mt-1 line-clamp-1">{{ \Illuminate\Support\Str::limit($doctor->bio, 100) }}</p>
                                                @endif
                                            </div>
                                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    @endforeach
                                </div>
                                @error('doctor_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            @endif
                        @endif

                        {{-- STEP 2: Date & time --}}
                        @if($step === 2)
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.select_date') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.select_date') }}</label>
                                    <input type="date"
                                           wire:model.live="selectedDate"
                                           min="{{ $this->minBookableDate }}"
                                           max="{{ $this->maxBookableDate }}"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('selectedDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.select_time') }}</label>
                                    @if(! $selectedDate)
                                        <p class="text-sm text-gray-400 py-2">—</p>
                                    @elseif(empty($this->availableSlots))
                                        <p class="text-sm text-amber-600 py-2">{{ __('booking.no_slots_available') }}</p>
                                    @else
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-60 overflow-y-auto">
                                            @foreach($this->availableSlots as $slot)
                                                <button type="button" wire:click="selectSlot('{{ $slot }}')"
                                                        class="px-3 py-2 text-sm rounded-lg border transition
                                                               {{ $selectedTime === $slot ? 'btn-clinic-primary border-clinic-primary' : 'border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                                                    {{ $slot }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                    @error('selectedTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-between mt-6">
                                <button type="button" wire:click="previousStep" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                    ← {{ __('booking.back') }}
                                </button>
                                <button type="button" wire:click="nextStep" @disabled(!$selectedDate || !$selectedTime)
                                        class="btn-clinic-primary px-5 py-2 rounded-lg text-sm font-semibold">
                                    {{ __('booking.next') }} →
                                </button>
                            </div>
                        @endif

                        {{-- STEP 3: Patient details --}}
                        @if($step === 3)
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.your_details') }}</h2>
                            <form wire:submit="submitBooking" class="space-y-4">
                                {{-- Honeypot --}}
                                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                                    <label>Website<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.first_name') }} *</label>
                                        <input type="text" wire:model="first_name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.last_name') }} *</label>
                                        <input type="text" wire:model="last_name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.phone_number') }} *</label>
                                        <input type="tel" wire:model="phone" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.email_optional') }}</label>
                                        <input type="email" wire:model="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('booking.reason_for_visit') }}</label>
                                    <textarea wire:model="reason" rows="3" placeholder="{{ __('booking.reason_placeholder') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="flex items-start space-x-2">
                                        <input type="checkbox" wire:model="accept_terms" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-600">{{ __('booking.accept_terms') }} *</span>
                                    </label>
                                    @error('accept_terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Recap --}}
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-sm">
                                    @php $selectedDoctor = $this->doctors->firstWhere('id', $doctor_id); @endphp
                                    <div class="flex justify-between mb-1"><span class="text-gray-500">{{ __('booking.appointment_with') }}</span><span class="font-medium">{{ $selectedDoctor?->name }}</span></div>
                                    <div class="flex justify-between mb-1"><span class="text-gray-500">{{ __('booking.select_date') }}</span><span class="font-medium">{{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') : '' }}</span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">{{ __('booking.select_time') }}</span><span class="font-medium">{{ $selectedTime }}</span></div>
                                </div>

                                <div class="flex justify-between pt-2">
                                    <button type="button" wire:click="previousStep" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                        ← {{ __('booking.back') }}
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled" class="btn-clinic-primary px-5 py-2 rounded-lg text-sm font-semibold">
                                        <span wire:loading.remove wire:target="submitBooking">{{ __('booking.confirm_booking') }}</span>
                                        <span wire:loading wire:target="submitBooking">…</span>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    @endif

    </div>{{-- end #booking --}}

</div>
