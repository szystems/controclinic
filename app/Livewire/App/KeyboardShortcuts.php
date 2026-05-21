<?php

namespace App\Livewire\App;

use App\Models\Clinic;
use Livewire\Component;

class KeyboardShortcuts extends Component
{
    public Clinic $clinic;

    public function getShortcutsProperty(): array
    {
        $slug = $this->clinic->slug;
        $user = auth()->user();

        $navigate = [
            ['key' => 'g d', 'label' => __('shortcuts.go_dashboard'),    'url' => route('app.dashboard', ['clinic' => $slug]), 'permission' => null],
            ['key' => 'g p', 'label' => __('shortcuts.go_patients'),     'url' => route('app.patients.index', ['clinic' => $slug]), 'permission' => 'patients.view'],
            ['key' => 'g a', 'label' => __('shortcuts.go_appointments'), 'url' => route('app.appointments.index', ['clinic' => $slug]), 'permission' => 'appointments.view'],
            ['key' => 'g c', 'label' => __('shortcuts.go_calendar'),     'url' => route('app.appointments.calendar', ['clinic' => $slug]), 'permission' => 'appointments.view'],
            ['key' => 'g i', 'label' => __('shortcuts.go_invoices'),     'url' => route('app.invoices.index', ['clinic' => $slug]), 'permission' => 'invoices.view'],
            ['key' => 'g r', 'label' => __('shortcuts.go_reports'),      'url' => route('app.reports', ['clinic' => $slug]), 'permission' => 'reports.view'],
        ];

        return [
            'navigate' => array_values(array_filter($navigate, fn ($s) => $s['permission'] === null || $user->can($s['permission']))),
            'actions' => [
                ['key' => '?', 'label' => __('shortcuts.show_shortcuts'), 'url' => null],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.app.keyboard-shortcuts', [
            'shortcuts' => $this->shortcuts,
        ]);
    }
}
