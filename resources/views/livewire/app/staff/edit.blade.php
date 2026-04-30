<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6">
            <a href="{{ route('app.staff.index', ['clinic' => $member->clinic->slug]) }}"
               wire:navigate
               class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('staff.back_to_list') }}
            </a>
        </nav>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('staff.edit_member') }}: {{ $member->name }}
        </h1>

        {{-- Flash Messages --}}
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

        <form wire:submit="save" class="space-y-6">
            {{-- Personal Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('staff.personal_info') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" id="name"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" id="email"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.phone') }}
                        </label>
                        <input wire:model="phone" type="text" id="phone"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Role & Access --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('staff.role_and_access') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Role --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.role') }}
                        </label>
                        @if($member->isOwner())
                            {{-- Owner: rol bloqueado, no editable --}}
                            <div class="flex items-center gap-2 px-3 py-2 bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-700 rounded-lg">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300">
                                    {{ __('staff.role_owner') }}
                                </span>
                                <span class="text-xs text-purple-600 dark:text-purple-400">{{ __('staff.owner_role_locked') }}</span>
                            </div>
                        @else
                            <select wire:model.live="role" id="role"
                                    class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="doctor">{{ __('staff.role_doctor') }}</option>
                                <option value="assistant">{{ __('staff.role_assistant') }}</option>
                                <option value="secretary">{{ __('staff.role_secretary') }}</option>
                                <option value="receptionist">{{ __('staff.role_receptionist') }}</option>
                            </select>
                            @error('role') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.status') }}
                        </label>
                        <select wire:model="is_active" id="is_active"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="1">{{ __('staff.active') }}</option>
                            <option value="0">{{ __('staff.inactive') }}</option>
                        </select>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.password') }}
                        </label>
                        <input wire:model="password" type="password" id="password"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('staff.leave_blank') }}</p>
                        @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password Confirmation --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.password_confirmation') }}
                        </label>
                        <input wire:model="password_confirmation" type="password" id="password_confirmation"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('password_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Forzar reset de contraseña y transferir ownership --}}
                    @php $authUser = auth()->user(); @endphp

                    @if(($authUser->isOwner() || $authUser->isAdmin()) && $authUser->id !== $member->id)
                        <div class="md:col-span-2">
                            <div class="mt-2 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 mb-0.5">{{ __('staff.force_reset_title') }}</p>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mb-3">{{ __('staff.force_reset_description') }}</p>
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="sendResetPasswordLink"
                                            wire:loading.attr="disabled" wire:target="sendResetPasswordLink"
                                            class="px-3 py-1.5 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 transition disabled:opacity-60">
                                        <span wire:loading.remove wire:target="sendResetPasswordLink">{{ __('staff.force_password_reset') }}</span>
                                        <span wire:loading wire:target="sendResetPasswordLink">{{ __('general.sending') }}...</span>
                                    </button>
                                    @if($resetLinkSent)
                                        <span class="text-xs font-medium text-green-600 dark:text-green-400">&#10003; {{ __('staff.reset_link_sent') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($authUser->isOwner() && $member->id !== $authUser->id && $member->clinic->owner_id === $authUser->id && $member->role === 'doctor')
                        <div class="md:col-span-2 mt-4" x-data="{ confirming: false }">
                            @if($ownershipTransferred)
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">&#10003; {{ __('staff.ownership_transferred') }}</span>
                            @else
                                <button type="button" x-show="!confirming" @click="confirming = true"
                                        class="px-4 py-2 text-sm font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition">
                                    {{ __('staff.transfer_ownership') }}
                                </button>
                                <div x-show="confirming" x-cloak
                                     class="mt-2 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-lg">
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">&#9888; {{ __('staff.transfer_ownership_section') }}</p>
                                    <p class="text-sm text-red-600 dark:text-red-400 mb-3">{{ __('staff.transfer_confirm') }}</p>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="transferOwnership"
                                                class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                            {{ __('staff.transfer_confirm_yes') }}
                                        </button>
                                        <button type="button" @click="confirming = false"
                                                class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                            {{ __('general.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Permissions Preview --}}
            <x-role-permissions-preview :role="$role" />

            {{-- Permisos personalizados (extras) --}}
            @if(! $member->isOwner())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('staff.custom_permissions_title') }}</h2>
                    </div>
                    @if(count($extraPermissions) > 0)
                        <button type="button"
                                wire:click="restoreRolePermissions"
                                wire:confirm="{{ __('staff.restore_permissions_confirm') }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('staff.restore_role_permissions') }}
                        </button>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('staff.custom_permissions_note') }}</p>

                <div class="space-y-4">
                    @foreach($permissionCatalog as $module => $perms)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('staff.module_'.$module) }}
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($perms as $perm)
                                    @php $isRolePerm = in_array($perm, $rolePermissions); @endphp
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-md border {{ $isRolePerm ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' : 'bg-gray-50 dark:bg-gray-700/40 border-gray-200 dark:border-gray-600' }} text-xs">
                                        <input type="checkbox"
                                               value="{{ $perm }}"
                                               @checked($isRolePerm || in_array($perm, $extraPermissions))
                                               @disabled($isRolePerm)
                                               wire:model="extraPermissions"
                                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 disabled:opacity-60">
                                        <span class="text-[11px] text-gray-700 dark:text-gray-200">{{ __('permissions.'.$perm) }}</span>
                                        @if($isRolePerm)
                                            <span class="ml-auto text-[10px] text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">{{ __('staff.role_default') }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Professional Info (shown for doctors) --}}
            @if($role === 'doctor')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('staff.professional_info') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Specialties --}}
                    <div>
                        <label for="specialties" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.specialties') }}
                        </label>
                        <input wire:model="specialties" type="text" id="specialties"
                               placeholder="{{ __('staff.specialties_placeholder') }}"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('specialties') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- License Number --}}
                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.license_number') }}
                        </label>
                        <input wire:model="license_number" type="text" id="license_number"
                               placeholder="{{ __('staff.license_placeholder') }}"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('license_number') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.bio') }}
                        </label>
                        <textarea wire:model="bio" id="bio" rows="3"
                                  placeholder="{{ __('staff.bio_placeholder') }}"
                                  class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        @error('bio') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            @endif

            {{-- Transferir ownership desde el propio perfil del owner --}}
            @if($member->isOwner() && auth()->id() === $member->id)
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 p-6">
                <h2 class="text-lg font-semibold text-red-800 dark:text-red-300 mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    {{ __('staff.transfer_ownership_section') }}
                </h2>
                <p class="text-sm text-red-600 dark:text-red-400 mb-4">{{ __('staff.transfer_ownership_description') }}</p>

                @php $candidates = $this->transferCandidates; @endphp

                @if($candidates->isEmpty())
                    <p class="text-sm text-red-500 dark:text-red-400 italic">{{ __('staff.no_transfer_candidates') }}</p>
                @else
                    <div x-data="{ confirming: false }">
                        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-3" x-show="!confirming">
                            <div class="flex-1">
                                <label for="transferToId" class="block text-sm font-medium text-red-700 dark:text-red-300 mb-1">
                                    {{ __('staff.select_new_owner') }}
                                </label>
                                <select wire:model="transferToId" id="transferToId"
                                        class="block w-full border border-red-300 dark:border-red-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <option value="">{{ __('staff.select_member') }}</option>
                                    @foreach($candidates as $candidate)
                                        <option value="{{ $candidate->id }}">
                                            {{ $candidate->name }} &mdash; {{ $candidate->email }} ({{ __('staff.role_' . $candidate->role) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button"
                                    @click="$wire.transferToId ? confirming = true : null"
                                    :disabled="!$wire.transferToId"
                                    class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ __('staff.transfer_ownership') }}
                            </button>
                        </div>

                        <div x-show="confirming" x-cloak
                             class="mt-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-lg">
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">&#9888; {{ __('staff.transfer_ownership_section') }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400 mb-3">{{ __('staff.transfer_confirm') }}</p>
                            <div class="flex gap-2">
                                <button type="button" wire:click="transferOwnership"
                                        class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    {{ __('staff.transfer_confirm_yes') }}
                                </button>
                                <button type="button" @click="confirming = false"
                                        class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    {{ __('general.cancel') }}
                                </button>
                            </div>
                        </div>

                        @if($ownershipTransferred)
                            <p class="mt-3 text-sm font-medium text-green-600 dark:text-green-400">&#10003; {{ __('staff.ownership_transferred') }}</p>
                        @endif
                    </div>
                @endif
            </div>
            @endif

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('app.staff.index', ['clinic' => $member->clinic->slug]) }}"
                   wire:navigate
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <span wire:loading.remove wire:target="save">{{ __('general.save') }}</span>
                    <span wire:loading wire:target="save">{{ __('general.saving') }}...</span>
                </button>
            </div>
        </form>
    </div>
</div>
