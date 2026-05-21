<?php

namespace App\Livewire\App\Help;

use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    /** Modules available in the help centre */
    public static function modules(): array
    {
        return [
            'patients',
            'appointments',
            'medical-records',
            'invoices',
            'prescriptions',
            'staff',
            'reports',
            'schedule',
        ];
    }

    public function render()
    {
        return view('livewire.app.help.index')
            ->layout('layouts.app');
    }
}
