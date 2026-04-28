<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.plans.index', [
            'plans' => Plan::ordered()->withCount('clinics')->get(),
        ])->layout('layouts.admin');
    }
}
