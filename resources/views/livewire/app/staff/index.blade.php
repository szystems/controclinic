<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('staff.title') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('staff.subtitle') }}
                </p>
            </div>
            @can('users.manage')
            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                @can('users.print')
                    <button type="button" wire:click="exportPdf"
                            class="inline-flex items-center gap-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('general.export_pdf') }}
                    </button>
                @endcan
                @php
                    $limits = $currentClinic->getPlanLimits();
                    $canAddDoctor = $currentClinic->canAddDoctor();
                    $canAddStaff = $currentClinic->canAddStaff();
                    $canAddAny = $canAddDoctor || $canAddStaff;
                @endphp
                @if($canAddAny)
                    <a href="{{ route('app.staff.create', ['clinic' => $currentClinic->slug]) }}"
                       wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('staff.add_member') }}
                    </a>
                @else
                    <x-upgrade-nudge type="button" :clinic-slug="$currentClinic->slug" />
                @endif
            </div>
            @endcan
        </div>

        {{-- Usage Badges --}}
        <div class="flex flex-wrap gap-3 mb-6">
            @php
                $doctorCount = $this->doctorCount;
                $staffCount = $this->staffCount;
                $maxDoctors = $limits['max_doctors'] ?? null;
                $maxStaff = $limits['max_staff'] ?? null;
            @endphp
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                @if($maxDoctors === null)
                    {{ __('staff.doctors_unlimited', ['current' => $doctorCount]) }}
                @else
                    {{ __('staff.doctors_usage', ['current' => $doctorCount, 'max' => $maxDoctors]) }}
                @endif
                @if($maxDoctors !== null && !$canAddDoctor)
                    <x-upgrade-nudge type="inline" :clinic-slug="$currentClinic->slug" :message="__('staff.upgrade_for_doctors')" />
                @endif
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                @if($maxStaff === null)
                    {{ __('staff.staff_unlimited', ['current' => $staffCount]) }}
                @elseif($maxStaff === 0)
                    {{ __('staff.no_staff_in_plan') }}
                @else
                    {{ __('staff.staff_usage', ['current' => $staffCount, 'max' => $maxStaff]) }}
                @endif
                @if($maxStaff !== null && $maxStaff > 0 && !$canAddStaff)
                    <x-upgrade-nudge type="inline" :clinic-slug="$currentClinic->slug" :message="__('staff.upgrade_for_staff')" />
                @endif
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
                    {{ session('success') }}
                </p>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </p>
            </div>
        </div>
        @endif

        {{-- Pending Invitations --}}
        @can('users.manage')
        @if($pendingInvitations->count())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('invitations.pending_invitations') }}
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                        {{ $pendingInvitations->count() }}
                    </span>
                </h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($pendingInvitations as $invitation)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $invitation->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $invitation->email }}
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $invitation->role === 'doctor' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                            {{ __('staff.role_' . $invitation->role) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                            {{ __('invitations.status_pending') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('invitations.expires', ['date' => $invitation->expires_at->diffForHumans()]) }}
                        </span>
                        <button wire:click="resendInvitation('{{ $invitation->id }}')"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                            {{ __('invitations.resend') }}
                        </button>
                        <button wire:click="cancelInvitation('{{ $invitation->id }}')"
                                wire:confirm="{{ __('invitations.confirm_cancel', ['name' => $invitation->name]) }}"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 text-sm font-medium">
                            {{ __('general.cancel') }}
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endcan

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label for="search" class="sr-only">{{ __('general.search') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search"
                               type="text"
                               id="search"
                               placeholder="{{ __('staff.search_placeholder') }}"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- Role Filter --}}
                <div>
                    <label for="roleFilter" class="sr-only">{{ __('staff.role') }}</label>
                    <select wire:model.live="roleFilter"
                            id="roleFilter"
                            class="block w-full py-2 pl-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">{{ __('staff.all_roles') }}</option>
                        <option value="doctor">{{ __('staff.role_doctor') }}</option>
                        <option value="assistant">{{ __('staff.role_assistant') }}</option>
                        <option value="secretary">{{ __('staff.role_secretary') }}</option>
                        <option value="receptionist">{{ __('staff.role_receptionist') }}</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="statusFilter" class="sr-only">{{ __('staff.status') }}</label>
                    <select wire:model.live="statusFilter"
                            id="statusFilter"
                            class="block w-full py-2 pl-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">{{ __('staff.all_statuses') }}</option>
                        <option value="active">{{ __('staff.active') }}</option>
                        <option value="inactive">{{ __('staff.inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($members->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th wire:click="sortBy('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                                    <div class="flex items-center gap-1">
                                        {{ __('staff.name') }}
                                        @if($sortField === 'name')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                @if($sortDirection === 'asc')
                                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                @else
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('staff.role') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('staff.status') }}
                                </th>
                                <th wire:click="sortBy('last_login_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                                    <div class="flex items-center gap-1">
                                        {{ __('staff.last_login') }}
                                        @if($sortField === 'last_login_at')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                @if($sortDirection === 'asc')
                                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                @else
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                @can('users.manage')
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('staff.actions') }}
                                </th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($members as $member)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="{{ $member->avatar_url }}" alt="{{ $member->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                                    {{ $member->name }}
                                                    @if($member->id === auth()->id())
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                                            {{ __('staff.you') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div x-data="{
                                                showPerms: false,
                                                popoverStyle: {},
                                                openPopover() {
                                                    this.showPerms = !this.showPerms;
                                                    if (this.showPerms) {
                                                        this.$nextTick(() => {
                                                            const btn = this.$refs.btn.getBoundingClientRect();
                                                            const popover = this.$refs.popover;
                                                            const popoverHeight = popover.offsetHeight;
                                                            const spaceBelow = window.innerHeight - btn.bottom;
                                                            const openAbove = spaceBelow < popoverHeight + 10;
                                                            this.popoverStyle = {
                                                                position: 'fixed',
                                                                left: btn.left + 'px',
                                                                zIndex: 9999,
                                                                ...(openAbove
                                                                    ? { bottom: (window.innerHeight - btn.top + 4) + 'px' }
                                                                    : { top: (btn.bottom + 4) + 'px' }
                                                                ),
                                                            };
                                                        });
                                                    }
                                                }
                                             }" class="relative inline-block">
                                            <button x-ref="btn"
                                                    @click="openPopover()"
                                                    @click.outside="showPerms = false"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition
                                                        @if($member->role === 'owner') bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/60
                                                        @elseif($member->role === 'doctor') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/60
                                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                                                        @endif">
                                                {{ __('staff.role_' . $member->role) }}
                                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>

                                            {{-- Permissions Popover --}}
                                            <div x-ref="popover"
                                                 x-show="showPerms"
                                                 x-bind:style="popoverStyle"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3"
                                                 x-cloak>
                                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('staff.permissions_title') }}</p>
                                                @php
                                                    $memberPerms = [
                                                        'owner' => [
                                                            __('staff.module_patients') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_appointments') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_records') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_settings') => [__('staff.perm_view'), __('staff.perm_edit')],
                                                        ],
                                                        'doctor' => [
                                                            __('staff.module_patients') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_appointments') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_records') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_settings') => [__('staff.perm_view')],
                                                        ],
                                                        'assistant' => [
                                                            __('staff.module_patients') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                            __('staff.module_appointments') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit')],
                                                        ],
                                                        'secretary' => [
                                                            __('staff.module_patients') => [__('staff.perm_view'), __('staff.perm_create')],
                                                            __('staff.module_appointments') => [__('staff.perm_view'), __('staff.perm_create'), __('staff.perm_edit'), __('staff.perm_view_all')],
                                                        ],
                                                        'receptionist' => [
                                                            __('staff.module_patients') => [__('staff.perm_view')],
                                                            __('staff.module_appointments') => [__('staff.perm_view'), __('staff.perm_edit')],
                                                        ],
                                                    ];
                                                    $permsForRole = $memberPerms[$member->role] ?? [];
                                                @endphp
                                                <div class="space-y-2">
                                                    @foreach($permsForRole as $module => $perms)
                                                        <div>
                                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $module }}</span>
                                                            <div class="flex flex-wrap gap-1 mt-0.5">
                                                                @foreach($perms as $perm)
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                                        {{ $perm }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @if($member->isOwner())
                                        <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                            {{ __('staff.role_doctor') }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $member->is_active ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $member->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                            {{ $member->is_active ? __('staff.active') : __('staff.inactive') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $member->last_login_at ? $member->last_login_at->diffForHumans() : __('staff.never') }}
                                    </td>
                                    @can('users.manage')
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($member->isOwner())
                                                {{-- Owner: sin acciones destructivas, sólo edit perfil --}}
                                                <a href="{{ route('app.staff.edit', ['clinic' => $currentClinic->slug, 'user' => $member->id]) }}"
                                                   wire:navigate
                                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                                   title="{{ __('general.edit') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300"
                                                      title="{{ __('staff.cannot_delete_owner') }}">
                                                    {{ __('staff.owner_badge') }}
                                                </span>
                                            @else
                                            <a href="{{ route('app.staff.edit', ['clinic' => $currentClinic->slug, 'user' => $member->id]) }}"
                                               wire:navigate
                                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                               title="{{ __('general.edit') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <button wire:click="toggleStatus({{ $member->id }})"
                                                    wire:confirm="{{ $member->is_active ? __('staff.confirm_deactivate', ['name' => $member->name]) : __('staff.confirm_activate', ['name' => $member->name]) }}"
                                                    class="{{ $member->is_active ? 'text-amber-600 dark:text-amber-400 hover:text-amber-900' : 'text-green-600 dark:text-green-400 hover:text-green-900' }}"
                                                    title="{{ $member->is_active ? __('staff.inactive') : __('staff.active') }}">
                                                @if($member->is_active)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                            @if($member->id !== auth()->id())
                                            <button wire:click="deleteMember({{ $member->id }})"
                                                    wire:confirm="{{ __('staff.confirm_delete', ['name' => $member->name]) }}"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                    title="{{ __('general.delete') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                            @endif
                                            @endif
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($members->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $members->links() }}
                </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @if($search || $roleFilter || $statusFilter !== '')
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('staff.no_results') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('staff.no_results_description') }}</p>
                    @else
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('staff.no_members') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('staff.no_members_description') }}</p>
                        @can('users.manage')
                        <div class="mt-6">
                            @if($currentClinic->canAddDoctor() || $currentClinic->canAddStaff())
                                <a href="{{ route('app.staff.create', ['clinic' => $currentClinic->slug]) }}"
                                   wire:navigate
                                   class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-hover transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('staff.add_member') }}
                                </a>
                            @else
                                <x-upgrade-nudge type="button" :clinic-slug="$currentClinic->slug" />
                            @endif
                        </div>
                        @endcan
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
