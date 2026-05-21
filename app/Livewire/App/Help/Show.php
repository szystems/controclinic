<?php

namespace App\Livewire\App\Help;

use Livewire\Component;

class Show extends Component
{
    public string $module = '';

    public array $moduleData = [];

    /** Valid module slugs */
    private const VALID_MODULES = [
        'patients', 'appointments', 'medical-records',
        'invoices', 'prescriptions', 'staff', 'reports', 'schedule',
    ];

    public function mount(string $module): void
    {
        abort_unless(in_array($module, self::VALID_MODULES, true), 404);
        $this->module = $module;
    }

    public function render()
    {
        return view('livewire.app.help.show')
            ->layout('layouts.app');
    }
}
