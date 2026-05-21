<?php

namespace App\Livewire\App\Onboarding;

use App\Models\Clinic;
use App\Models\Plan;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public Clinic $clinic;

    public int $currentStep = 1;

    public int $totalSteps = 5;

    // Step 1: Clinic Info
    public string $phone_country = 'GT';

    public string $phone_number = '';

    public string $address = '';

    public string $city = '';

    public string $country = 'GT';

    // Step 2: Localization
    public string $timezone = 'America/Guatemala';

    public string $currency = 'USD';

    public string $locale = 'es';

    // Step 3: Branding
    public string $primary_color = '#4f46e5';

    public string $secondary_color = '#10b981';

    public $logo = null;

    public ?string $currentLogo = null;

    // Step 4: Working Hours
    public array $working_days = [1, 2, 3, 4, 5];

    public bool $has_split_shift = true;

    public string $weekday_shift1_start = '08:00';

    public string $weekday_shift1_end = '13:00';

    public string $weekday_shift2_start = '14:00';

    public string $weekday_shift2_end = '18:00';

    public bool $works_weekends = false;

    public array $weekend_days = [];

    public string $weekend_shift1_start = '08:00';

    public string $weekend_shift1_end = '13:00';

    public bool $weekend_has_split_shift = false;

    public string $weekend_shift2_start = '14:00';

    public string $weekend_shift2_end = '17:00';

    // Step 5: Plan selection
    public string $selectedPlan = 'free';

    public const PHONE_CODES = [
        'GT' => ['code' => '+502', 'flag' => '🇬🇹', 'name' => 'Guatemala'],
        'MX' => ['code' => '+52', 'flag' => '🇲🇽', 'name' => 'México'],
        'CO' => ['code' => '+57', 'flag' => '🇨🇴', 'name' => 'Colombia'],
        'AR' => ['code' => '+54', 'flag' => '🇦🇷', 'name' => 'Argentina'],
        'CL' => ['code' => '+56', 'flag' => '🇨🇱', 'name' => 'Chile'],
        'PE' => ['code' => '+51', 'flag' => '🇵🇪', 'name' => 'Perú'],
        'EC' => ['code' => '+593', 'flag' => '🇪🇨', 'name' => 'Ecuador'],
        'ES' => ['code' => '+34', 'flag' => '🇪🇸', 'name' => 'España'],
        'US' => ['code' => '+1', 'flag' => '🇺🇸', 'name' => 'EE.UU.'],
        'CA' => ['code' => '+1', 'flag' => '🇨🇦', 'name' => 'Canadá'],
        'CR' => ['code' => '+506', 'flag' => '🇨🇷', 'name' => 'Costa Rica'],
        'PA' => ['code' => '+507', 'flag' => '🇵🇦', 'name' => 'Panamá'],
        'HN' => ['code' => '+504', 'flag' => '🇭🇳', 'name' => 'Honduras'],
        'SV' => ['code' => '+503', 'flag' => '🇸🇻', 'name' => 'El Salvador'],
        'NI' => ['code' => '+505', 'flag' => '🇳🇮', 'name' => 'Nicaragua'],
        'DO' => ['code' => '+1', 'flag' => '🇩🇴', 'name' => 'Rep. Dominicana'],
        'VE' => ['code' => '+58', 'flag' => '🇻🇪', 'name' => 'Venezuela'],
        'BO' => ['code' => '+591', 'flag' => '🇧🇴', 'name' => 'Bolivia'],
        'PY' => ['code' => '+595', 'flag' => '🇵🇾', 'name' => 'Paraguay'],
        'UY' => ['code' => '+598', 'flag' => '🇺🇾', 'name' => 'Uruguay'],
    ];

    public function getPlansProperty()
    {
        return Plan::active()->ordered()->get();
    }

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;

        // Parse phone
        $this->parsePhone($clinic->phone);
        $this->address = $clinic->address ?? '';
        $this->city = $clinic->city ?? '';
        $this->country = $clinic->country ?? 'GT';

        if (! $this->phone_number) {
            $this->phone_country = $this->country;
        }

        $this->timezone = $clinic->timezone ?? 'America/Guatemala';
        $this->currency = $clinic->currency ?? 'USD';
        $this->locale = $clinic->locale ?? 'es';

        $branding = $clinic->branding ?? [];
        $this->primary_color = $branding['primary_color'] ?? '#4f46e5';
        $this->secondary_color = $branding['secondary_color'] ?? '#10b981';
        $this->currentLogo = $branding['logo'] ?? null;

        $settings = $clinic->settings ?? [];

        // Separate weekdays from weekend days
        $allDays = $settings['working_days'] ?? [1, 2, 3, 4, 5];
        $this->working_days = array_values(array_filter($allDays, fn ($d) => $d >= 1 && $d <= 5));
        $this->weekend_days = array_values(array_filter($allDays, fn ($d) => $d === 0 || $d === 6));
        $this->works_weekends = ! empty($this->weekend_days);

        // Load schedule settings (new format or fallback to old)
        $this->has_split_shift = $settings['has_split_shift'] ?? true;
        $this->weekday_shift1_start = $settings['weekday_shift1_start'] ?? '08:00';
        $this->weekday_shift1_end = $settings['weekday_shift1_end'] ?? '13:00';
        $this->weekday_shift2_start = $settings['weekday_shift2_start'] ?? '14:00';
        $this->weekday_shift2_end = $settings['weekday_shift2_end'] ?? '18:00';

        $this->weekend_shift1_start = $settings['weekend_shift1_start'] ?? '08:00';
        $this->weekend_shift1_end = $settings['weekend_shift1_end'] ?? '13:00';
        $this->weekend_has_split_shift = $settings['weekend_has_split_shift'] ?? false;
        $this->weekend_shift2_start = $settings['weekend_shift2_start'] ?? '14:00';
        $this->weekend_shift2_end = $settings['weekend_shift2_end'] ?? '17:00';

        $this->selectedPlan = $clinic->plan_type ?? 'free';
    }

    private function parsePhone(?string $phone): void
    {
        if (! $phone) {
            return;
        }

        // Sort codes by length (longest first) to match most specific
        $sorted = collect(self::PHONE_CODES)->sortByDesc(fn ($d) => strlen($d['code']));

        foreach ($sorted as $countryCode => $data) {
            if (str_starts_with($phone, $data['code'])) {
                $this->phone_country = $countryCode;
                $this->phone_number = trim(substr($phone, strlen($data['code'])));

                return;
            }
        }

        $this->phone_number = $phone;
    }

    public function updatedCountry($value): void
    {
        if (isset(self::PHONE_CODES[$value])) {
            $this->phone_country = $value;
        }
    }

    public function updatedWorkingDays(): void
    {
        $this->working_days = array_values(array_map('intval', $this->working_days));
    }

    public function updatedWeekendDays(): void
    {
        $this->weekend_days = array_values(array_map('intval', $this->weekend_days));
    }

    public function updatedWorksWeekends($value): void
    {
        if (! $value) {
            $this->weekend_days = [];
        }
    }

    public function selectPlan(string $plan): void
    {
        if (in_array($plan, ['free', 'solo', 'group', 'enterprise'])) {
            $this->selectedPlan = $plan;
        }
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->saveCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function completeOnboarding(): void
    {
        $updateData = ['onboarding_completed_at' => now()];

        // Save desired plan preference for when billing is available
        if ($this->selectedPlan !== 'free') {
            $updateData['settings'] = array_merge($this->clinic->settings ?? [], [
                'desired_plan' => $this->selectedPlan,
            ]);
        }

        $this->clinic->update($updateData);

        $this->redirect(route('app.dashboard', $this->clinic->slug), navigate: true);
    }

    public function skipOnboarding(): void
    {
        $this->clinic->update([
            'onboarding_completed_at' => now(),
        ]);

        $this->redirect(route('app.dashboard', $this->clinic->slug), navigate: true);
    }

    public function skipStep(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function removeLogo(): void
    {
        if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
            Storage::disk('public')->delete($this->currentLogo);
        }

        $branding = $this->clinic->branding ?? [];
        unset($branding['logo']);
        $this->clinic->update(['branding' => $branding]);

        $this->currentLogo = null;
    }

    private function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate([
                'phone_country' => ['required', 'string', 'size:2'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['required', 'string', 'size:2'],
            ]),
            2 => $this->validate([
                'timezone' => ['required', 'string', 'timezone'],
                'currency' => ['required', 'string', 'size:3'],
                'locale' => ['required', 'string', 'in:es,en'],
            ]),
            3 => $this->validate([
                'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'logo' => ['nullable', 'image', 'max:2048'],
            ]),
            4 => $this->validateSchedule(),
            default => null,
        };
    }

    private function validateSchedule(): void
    {
        $rules = [
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => ['integer', 'between:1,5'],
            'weekday_shift1_start' => ['required', 'date_format:H:i'],
            'weekday_shift1_end' => ['required', 'date_format:H:i', 'after:weekday_shift1_start'],
        ];

        if ($this->has_split_shift) {
            $rules['weekday_shift2_start'] = ['required', 'date_format:H:i', 'after:weekday_shift1_end'];
            $rules['weekday_shift2_end'] = ['required', 'date_format:H:i', 'after:weekday_shift2_start'];
        }

        if ($this->works_weekends) {
            $rules['weekend_days'] = ['required', 'array', 'min:1'];
            $rules['weekend_days.*'] = ['integer', 'in:0,6'];
            $rules['weekend_shift1_start'] = ['required', 'date_format:H:i'];
            $rules['weekend_shift1_end'] = ['required', 'date_format:H:i', 'after:weekend_shift1_start'];

            if ($this->weekend_has_split_shift) {
                $rules['weekend_shift2_start'] = ['required', 'date_format:H:i', 'after:weekend_shift1_end'];
                $rules['weekend_shift2_end'] = ['required', 'date_format:H:i', 'after:weekend_shift2_start'];
            }
        }

        $this->validate($rules);
    }

    private function saveCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->clinic->update([
                'phone' => $this->phone_number
                    ? (self::PHONE_CODES[$this->phone_country]['code'].' '.$this->phone_number)
                    : null,
                'address' => $this->address ?: null,
                'city' => $this->city ?: null,
                'country' => $this->country,
            ]),
            2 => $this->clinic->update([
                'timezone' => $this->timezone,
                'currency' => $this->currency,
                'locale' => $this->locale,
            ]),
            3 => $this->clinic->update([
                'branding' => $this->buildBranding(),
            ]),
            4 => $this->saveSchedule(),
            default => null,
        };
    }

    private function saveSchedule(): void
    {
        $allWorkingDays = $this->working_days;
        if ($this->works_weekends) {
            $allWorkingDays = array_values(array_unique(array_merge($allWorkingDays, $this->weekend_days)));
        }

        $scheduleSettings = [
            'working_days' => $allWorkingDays,
            'has_split_shift' => $this->has_split_shift,
            'weekday_shift1_start' => $this->weekday_shift1_start,
            'weekday_shift1_end' => $this->weekday_shift1_end,
            'weekday_shift2_start' => $this->has_split_shift ? $this->weekday_shift2_start : null,
            'weekday_shift2_end' => $this->has_split_shift ? $this->weekday_shift2_end : null,
            'weekend_days' => $this->works_weekends ? $this->weekend_days : [],
            'weekend_shift1_start' => $this->works_weekends ? $this->weekend_shift1_start : null,
            'weekend_shift1_end' => $this->works_weekends ? $this->weekend_shift1_end : null,
            'weekend_has_split_shift' => $this->works_weekends && $this->weekend_has_split_shift,
            'weekend_shift2_start' => ($this->works_weekends && $this->weekend_has_split_shift) ? $this->weekend_shift2_start : null,
            'weekend_shift2_end' => ($this->works_weekends && $this->weekend_has_split_shift) ? $this->weekend_shift2_end : null,
            // Backward compatibility
            'working_hours_start' => $this->weekday_shift1_start,
            'working_hours_end' => $this->has_split_shift ? $this->weekday_shift2_end : $this->weekday_shift1_end,
        ];

        $this->clinic->update([
            'settings' => array_merge($this->clinic->settings ?? [], $scheduleSettings),
        ]);
    }

    private function buildBranding(): array
    {
        $branding = $this->clinic->branding ?? [];
        $branding['primary_color'] = $this->primary_color;
        $branding['secondary_color'] = $this->secondary_color;

        if ($this->logo) {
            if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
                Storage::disk('public')->delete($this->currentLogo);
            }
            $path = $this->logo->store("clinics/{$this->clinic->id}/branding", 'public');
            $branding['logo'] = $path;
            $this->currentLogo = $path;
            $this->logo = null;
        }

        return $branding;
    }

    public function render()
    {
        return view('livewire.app.onboarding.index')
            ->layout('layouts.app');
    }
}
