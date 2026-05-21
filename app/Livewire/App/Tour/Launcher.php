<?php

namespace App\Livewire\App\Tour;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Tour\Launcher — controls the interactive onboarding tour.
 *
 * Responsibilities:
 *  - Decide whether to auto-start the tour on first load
 *  - Expose `completeTour()` and `skipTour()` Livewire actions
 *  - Expose `replayTour()` so the user can restart at any time
 *
 * Tour state is stored in the JSON `preferences` column on the User model:
 *   preferences['tour_completed_at'] — ISO-8601 timestamp or null
 *   preferences['tour_skipped_at']   — ISO-8601 timestamp or null
 */
class Launcher extends Component
{
    public bool $autoStart = false;

    public string $role = 'receptionist';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->role = $user->roles->first()?->name ?? 'receptionist';

        $prefs = $user->preferences ?? [];

        $tourDone = ! empty($prefs['tour_completed_at']);
        $tourSkipped = ! empty($prefs['tour_skipped_at']);

        // Auto-start only if the user has never completed or skipped the tour
        $this->autoStart = ! $tourDone && ! $tourSkipped;
    }

    /** Called from JS when the user finishes the last step */
    public function completeTour(): void
    {
        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs['tour_completed_at'] = now()->toIso8601String();
        $user->update(['preferences' => $prefs]);

        $this->autoStart = false;
    }

    /** Called from JS when the user clicks "Skip tour" */
    public function skipTour(): void
    {
        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs['tour_skipped_at'] = now()->toIso8601String();
        $user->update(['preferences' => $prefs]);

        $this->autoStart = false;
    }

    /** Called from the "Repetir tour" button in the user menu */
    public function replayTour(): void
    {
        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        unset($prefs['tour_completed_at'], $prefs['tour_skipped_at']);
        $user->update(['preferences' => $prefs]);

        // JS (tour.js handleReplay) handles starting the tour directly;
        // we only need to clear DB state here.
        $this->autoStart = true;
    }

    public function render()
    {
        return view('livewire.app.tour.launcher');
    }
}
