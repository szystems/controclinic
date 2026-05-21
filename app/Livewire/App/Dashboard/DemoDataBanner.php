<?php

namespace App\Livewire\App\Dashboard;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class DemoDataBanner extends Component
{
    public Clinic $clinic;

    public bool $hasDemo = false;

    public bool $isEmpty = false;

    public bool $loading = false;

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
        $this->refresh();
    }

    public function loadDemo(): void
    {
        $this->loading = true;

        Artisan::call('clinic:seed-demo', ['clinic' => $this->clinic->slug]);

        $this->refresh();
        $this->loading = false;

        $this->dispatch('demo-loaded');
    }

    public function clearDemo(): void
    {
        $this->loading = true;

        Artisan::call('clinic:seed-demo', [
            'clinic' => $this->clinic->slug,
            '--clear' => true,
        ]);

        $this->refresh();
        $this->loading = false;

        $this->dispatch('demo-cleared');
    }

    private function refresh(): void
    {
        $this->hasDemo = Patient::where('clinic_id', $this->clinic->id)
            ->where('is_demo', true)
            ->exists();

        $hasRealPatients = Patient::where('clinic_id', $this->clinic->id)
            ->where('is_demo', false)
            ->exists();

        $hasRealAppointments = Appointment::where('clinic_id', $this->clinic->id)
            ->where('is_demo', false)
            ->exists();

        $this->isEmpty = ! $hasRealPatients && ! $hasRealAppointments;
    }

    public function render()
    {
        return view('livewire.app.dashboard.demo-data-banner');
    }
}
