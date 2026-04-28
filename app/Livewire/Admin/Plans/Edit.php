<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Clinic;
use App\Models\Plan;
use Livewire\Component;

class Edit extends Component
{
    public Plan $plan;

    // Form fields
    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public ?int $max_patients = 25;

    public ?int $max_appointments_per_month = 5;

    public ?int $max_doctors = 1;

    public ?int $max_staff = 0;

    public ?int $max_storage_bytes = 524288000;

    public string $features_text = '';

    public ?string $monthly_price = null;

    public ?string $yearly_price = null;

    public ?string $paddle_monthly_price_id = null;

    public ?string $paddle_yearly_price_id = null;

    public ?string $paddle_product_id = null;

    public int $trial_days = 0;

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $is_popular = false;

    public bool $is_free = false;

    public bool $is_enterprise = false;

    // Toggles for unlimited
    public bool $unlimited_patients = false;

    public bool $unlimited_appointments = false;

    public bool $unlimited_doctors = false;

    public bool $unlimited_staff = false;

    public bool $unlimited_storage = false;

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = $plan->description ?? '';
        $this->max_patients = $plan->max_patients;
        $this->max_appointments_per_month = $plan->max_appointments_per_month;
        $this->max_doctors = $plan->max_doctors;
        $this->max_staff = $plan->max_staff;
        $this->max_storage_bytes = $plan->max_storage_bytes;
        $this->features_text = implode(', ', $plan->features ?? []);
        $this->monthly_price = $plan->monthly_price;
        $this->yearly_price = $plan->yearly_price;
        $this->paddle_monthly_price_id = $plan->paddle_monthly_price_id;
        $this->paddle_yearly_price_id = $plan->paddle_yearly_price_id;
        $this->paddle_product_id = $plan->paddle_product_id;
        $this->trial_days = $plan->trial_days ?? 0;
        $this->sort_order = $plan->sort_order ?? 0;
        $this->is_active = $plan->is_active ?? true;
        $this->is_popular = $plan->is_popular ?? false;
        $this->is_free = $plan->is_free ?? false;
        $this->is_enterprise = $plan->is_enterprise ?? false;

        $this->unlimited_patients = $plan->max_patients === null;
        $this->unlimited_appointments = $plan->max_appointments_per_month === null;
        $this->unlimited_doctors = $plan->max_doctors === null;
        $this->unlimited_staff = $plan->max_staff === null;
        $this->unlimited_storage = $plan->max_storage_bytes === null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,'.$this->plan->id,
            'description' => 'nullable|string|max:1000',
            'max_patients' => 'nullable|integer|min:0',
            'max_appointments_per_month' => 'nullable|integer|min:0',
            'max_doctors' => 'nullable|integer|min:0',
            'max_staff' => 'nullable|integer|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'paddle_monthly_price_id' => 'nullable|string|max:255',
            'paddle_yearly_price_id' => 'nullable|string|max:255',
            'paddle_product_id' => 'nullable|string|max:255',
            'trial_days' => 'required|integer|min:0',
            'sort_order' => 'required|integer|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $features = array_map('trim', explode(',', $this->features_text));
        $features = array_filter($features);

        $this->plan->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'max_patients' => $this->unlimited_patients ? null : $this->max_patients,
            'max_appointments_per_month' => $this->unlimited_appointments ? null : $this->max_appointments_per_month,
            'max_doctors' => $this->unlimited_doctors ? null : $this->max_doctors,
            'max_staff' => $this->unlimited_staff ? null : $this->max_staff,
            'max_storage_bytes' => $this->unlimited_storage ? null : $this->max_storage_bytes,
            'features' => array_values($features),
            'monthly_price' => $this->monthly_price ?: null,
            'yearly_price' => $this->yearly_price ?: null,
            'paddle_monthly_price_id' => $this->paddle_monthly_price_id ?: null,
            'paddle_yearly_price_id' => $this->paddle_yearly_price_id ?: null,
            'paddle_product_id' => $this->paddle_product_id ?: null,
            'trial_days' => $this->trial_days,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_popular' => $this->is_popular,
            'is_free' => $this->is_free,
            'is_enterprise' => $this->is_enterprise,
        ]);

        // Sync limits to clinics on this plan
        $this->syncClinicLimits();

        session()->flash('success', __('admin.plan_updated'));
        $this->redirect(route('admin.plans.index'), navigate: true);
    }

    private function syncClinicLimits(): void
    {
        Clinic::where('plan_id', $this->plan->id)->update([
            'max_patients' => $this->unlimited_patients ? null : $this->max_patients,
            'max_appointments_per_month' => $this->unlimited_appointments ? null : $this->max_appointments_per_month,
            'max_doctors' => $this->unlimited_doctors ? null : $this->max_doctors,
            'max_staff' => $this->unlimited_staff ? null : $this->max_staff,
            'max_storage_bytes' => $this->unlimited_storage ? null : $this->max_storage_bytes,
        ]);
    }

    public function getAffectedClinicsProperty(): int
    {
        return Clinic::where('plan_id', $this->plan->id)->count();
    }

    public function render()
    {
        return view('livewire.admin.plans.edit', [
            'affectedClinics' => $this->affectedClinics,
        ])->layout('layouts.admin');
    }
}
