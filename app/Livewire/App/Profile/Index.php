<?php

namespace App\Livewire\App\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use WithPagination;

    public User $user;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $locale = '';

    public string $timezone = '';

    public string $avatar = '';

    public string $specialties = '';

    public string $bio = '';

    public string $license_number = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->locale = $this->user->locale ?? '';
        $this->timezone = $this->user->timezone ?? '';
        $this->avatar = $this->user->avatar ?? '';
        $this->specialties = is_array($this->user->specialties) ? implode(', ', $this->user->specialties) : '';
        $this->bio = $this->user->bio ?? '';
        $this->license_number = $this->user->license_number ?? '';
    }

    public function updateProfile()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'locale' => ['nullable', 'string', Rule::in(['', 'es', 'en'])],
            'timezone' => ['nullable', 'string', Rule::in(array_merge([''], timezone_identifiers_list()))],
            'specialties' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'license_number' => ['nullable', 'string', 'max:100'],
        ]);

        $emailChanged = $this->user->email !== $validated['email'];

        $this->user->fill($validated);
        $this->user->specialties = $this->specialties ? array_map('trim', explode(',', $this->specialties)) : null;

        if ($emailChanged) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();

        // Send verification link to the new email so the user knows what to do
        if ($emailChanged) {
            try {
                $this->user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Notify the navigation component so user name in nav updates immediately
        $payload = json_encode(['name' => $this->user->name], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->js("window.dispatchEvent(new CustomEvent('profile-updated', {detail: {$payload}}));");
        $this->dispatch('notify', type: 'success', message: __('profile.updated_successfully'));
    }

    public function updatePassword()
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }
        $this->user->update([
            'password' => Hash::make($validated['password']),
        ]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('notify', type: 'success', message: __('profile.password_updated'));
    }

    public function getActivitiesProperty()
    {
        return Activity::query()
            ->where('causer_id', $this->user->id)
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.app.profile.index', [
            'activities' => $this->activities,
        ]);
    }
}
