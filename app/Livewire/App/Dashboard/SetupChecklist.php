<?php

namespace App\Livewire\App\Dashboard;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SetupChecklist extends Component
{
    public Clinic $clinic;

    public bool $collapsed = false;

    /**
     * Steps definition. Order matters for display.
     */
    public static array $STEPS = [
        'logo' => ['icon' => 'photo',        'perm' => null],
        'schedule' => ['icon' => 'clock',         'perm' => null],
        'patient' => ['icon' => 'users',         'perm' => 'patients.create'],
        'appointment' => ['icon' => 'calendar',      'perm' => 'appointments.create'],
        'staff' => ['icon' => 'user-add',      'perm' => 'users.manage'],
        'public_page' => ['icon' => 'globe',         'perm' => null],
    ];

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
        $prefs = Auth::user()->preferences ?? [];
        $this->collapsed = (bool) ($prefs['setup_checklist_collapsed'] ?? false);
    }

    public function toggleCollapse(): void
    {
        $this->collapsed = ! $this->collapsed;
        $this->persistPref('setup_checklist_collapsed', $this->collapsed);
    }

    public function dismiss(): void
    {
        $this->persistPref('setup_checklist_dismissed', true);
        $this->dispatch('setup-checklist-dismissed');
    }

    /**
     * Returns array of step_key => bool (completed).
     */
    public function getStepsStatusProperty(): array
    {
        $clinic = $this->clinic;
        $settings = $clinic->settings ?? [];
        $branding = $clinic->branding ?? [];

        return [
            'logo' => ! empty($branding['logo']),
            'schedule' => array_key_exists('working_days', $settings),
            'patient' => $clinic->patients()->exists(),
            'appointment' => $clinic->appointments()->exists(),
            'staff' => $clinic->users()->where('users.id', '!=', $clinic->owner_id)->exists(),
            'public_page' => ! empty($settings['description']) || ! empty($settings['website']),
        ];
    }

    public function getCompletedCountProperty(): int
    {
        return count(array_filter($this->stepsStatus));
    }

    public function getTotalProperty(): int
    {
        return count(self::$STEPS);
    }

    public function getProgressPercentProperty(): int
    {
        return $this->total > 0
            ? (int) round(($this->completedCount / $this->total) * 100)
            : 0;
    }

    public function getIsAllDoneProperty(): bool
    {
        return $this->completedCount >= $this->total;
    }

    public function getRouteForStepProperty(): array
    {
        $slug = $this->clinic->slug;

        return [
            'logo' => route('app.settings.index', $slug),
            'schedule' => route('app.settings.index', $slug),
            'patient' => route('app.patients.create', $slug),
            'appointment' => route('app.appointments.create', $slug),
            'staff' => route('app.staff.create', $slug),
            'public_page' => route('app.settings.index', $slug),
        ];
    }

    public function render()
    {
        return view('livewire.app.dashboard.setup-checklist', [
            'stepsStatus' => $this->stepsStatus,
            'completedCount' => $this->completedCount,
            'total' => $this->total,
            'progressPercent' => $this->progressPercent,
            'isAllDone' => $this->isAllDone,
            'routeForStep' => $this->routeForStep,
        ]);
    }

    // ─── Private ────────────────────────────────────────────────────────────────

    private function persistPref(string $key, mixed $value): void
    {
        /** @var User $user */
        $user = Auth::user();
        $prefs = $user->preferences ?? [];
        $prefs[$key] = $value;
        $user->update(['preferences' => $prefs]);
    }
}
