<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public ?string $monthly_price = null;

    public ?string $yearly_price = null;

    public int $sort_order = 10;

    public bool $is_active = true;

    public function updatedName(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($value);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|string|max:1000',
            'monthly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $plan = Plan::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'max_patients' => null,
            'max_appointments_per_month' => null,
            'max_doctors' => 1,
            'max_staff' => 1,
            'max_storage_bytes' => null,
            'features' => [],
            'monthly_price' => $this->monthly_price ?: null,
            'yearly_price' => $this->yearly_price ?: null,
            'trial_days' => 0,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_popular' => false,
            'is_free' => false,
            'is_enterprise' => false,
            'is_private' => false,
            'requires_code' => false,
        ]);

        session()->flash('success', __('admin.plan_created'));
        $this->redirect(route('admin.plans.edit', $plan), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.plans.create')->layout('layouts.admin');
    }
}
