<?php

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $clinic_name = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email', 'unique:clinics,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'clinic_name' => ['required', 'string', 'max:255', 'min:3'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Generate unique slug from clinic name
            $baseSlug = Str::slug($validated['clinic_name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Clinic::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Create the clinic
            $clinic = Clinic::create([
                'name' => $validated['clinic_name'],
                'slug' => $slug,
                'email' => $validated['email'],
                'plan_type' => 'free',
                'status' => 'active',
                'settings' => Clinic::getDefaultSettings(),
                'branding' => ['primary_color' => '#4f46e5', 'secondary_color' => '#10b981'],
            ]);

            // Create the user linked to clinic
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'clinic_id' => $clinic->id,
                'role' => User::ROLE_OWNER,
                'is_active' => true,
            ]);

            // Assign owner role (Spatie)
            $user->assignRole('owner');

            // Link owner to clinic
            $clinic->update(['owner_id' => $user->id]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        // Full page redirect (not SPA navigate) because session changes after login
        $this->redirect(route('verification.notice'));
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Clinic Name -->
        <div>
            <x-input-label for="clinic_name" :value="__('auth.clinic_name')" />
            <x-text-input wire:model="clinic_name" id="clinic_name" class="block mt-1 w-full" type="text" name="clinic_name" required autofocus autocomplete="organization" placeholder="{{ __('auth.clinic_name_placeholder') }}" />
            <x-input-error :messages="$errors->get('clinic_name')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('auth.full_name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('auth.password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Free Plan Info -->
        <div class="mt-4 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg border border-indigo-200 dark:border-indigo-800">
            <p class="text-sm text-indigo-700 dark:text-indigo-300">
                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('auth.free_plan_info') }}
            </p>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                {{ __('auth.already_registered') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('auth.create_clinic') }}
            </x-primary-button>
        </div>
    </form>
</div>
