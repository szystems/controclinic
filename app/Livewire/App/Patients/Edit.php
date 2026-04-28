<?php

namespace App\Livewire\App\Patients;

use App\Models\Patient;
use App\Models\User;
use Livewire\Component;

class Edit extends Component
{
    public Patient $patient;

    // Basic Info
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $phone_secondary = '';

    public ?string $birth_date = null;

    public string $gender = '';

    // Identification
    public string $id_type = '';

    public string $id_number = '';

    // Address
    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $postal_code = '';

    public string $country = 'GT';

    // Medical Info
    public string $blood_type = '';

    public string $allergies = '';

    public string $chronic_conditions = '';

    public string $current_medications = '';

    // Emergency Contact
    public string $emergency_name = '';

    public string $emergency_phone = '';

    public string $emergency_relationship = '';

    // Insurance
    public string $insurance_provider = '';

    public string $insurance_policy_number = '';

    // Other
    public ?string $primary_doctor_id = null;

    public string $notes = '';

    public function mount(Patient $patient): void
    {
        // Tenant isolation
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);

        $this->patient = $patient;

        // Load patient data
        $this->first_name = $patient->first_name;
        $this->last_name = $patient->last_name;
        $this->email = $patient->email ?? '';
        $this->phone = $patient->phone ?? '';
        $this->phone_secondary = $patient->phone_secondary ?? '';
        $this->birth_date = $patient->birth_date?->format('Y-m-d');
        $this->gender = $patient->gender ?? '';
        $this->id_type = $patient->id_type ?? '';
        $this->id_number = $patient->id_number ?? '';
        $this->address = $patient->address ?? '';
        $this->city = $patient->city ?? '';
        $this->state = $patient->state ?? '';
        $this->postal_code = $patient->postal_code ?? '';
        $this->country = $patient->country ?? 'GT';
        $this->blood_type = $patient->blood_type ?? '';
        $this->allergies = $patient->allergies ?? '';
        $this->chronic_conditions = $patient->chronic_conditions ?? '';
        $this->current_medications = $patient->current_medications ?? '';
        $this->primary_doctor_id = $patient->primary_doctor_id;
        $this->notes = $patient->notes ?? '';

        // Emergency contact
        $emergencyContacts = $patient->emergency_contacts ?? [];
        if (! empty($emergencyContacts)) {
            $this->emergency_name = $emergencyContacts[0]['name'] ?? '';
            $this->emergency_phone = $emergencyContacts[0]['phone'] ?? '';
            $this->emergency_relationship = $emergencyContacts[0]['relationship'] ?? '';
        }

        // Insurance
        $insuranceInfo = $patient->insurance_info ?? [];
        $this->insurance_provider = $insuranceInfo['provider'] ?? '';
        $this->insurance_policy_number = $insuranceInfo['policy_number'] ?? '';
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'phone_secondary' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'id_type' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'blood_type' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'chronic_conditions' => ['nullable', 'string', 'max:1000'],
            'current_medications' => ['nullable', 'string', 'max:1000'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_policy_number' => ['nullable', 'string', 'max:100'],
            'primary_doctor_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function getDoctorsProperty()
    {
        return User::where('clinic_id', auth()->user()->clinic_id)
            ->whereIn('role', ['doctor', 'owner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        if (! auth()->user()->can('patients.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->validate();

        $this->patient->update([
            'primary_doctor_id' => $this->primary_doctor_id ?: null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email ?: null,
            'phone' => $this->phone,
            'phone_secondary' => $this->phone_secondary ?: null,
            'birth_date' => $this->birth_date ?: null,
            'gender' => $this->gender ?: null,
            'id_type' => $this->id_type ?: null,
            'id_number' => $this->id_number ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'postal_code' => $this->postal_code ?: null,
            'country' => $this->country,
            'blood_type' => $this->blood_type ?: null,
            'allergies' => $this->allergies ?: null,
            'chronic_conditions' => $this->chronic_conditions ?: null,
            'current_medications' => $this->current_medications ?: null,
            'emergency_contacts' => $this->emergency_name ? [
                [
                    'name' => $this->emergency_name,
                    'phone' => $this->emergency_phone,
                    'relationship' => $this->emergency_relationship,
                ],
            ] : null,
            'insurance_info' => $this->insurance_provider ? [
                'provider' => $this->insurance_provider,
                'policy_number' => $this->insurance_policy_number,
            ] : null,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('success', __('patients.updated_successfully'));

        $this->dispatch('patientUpdated');

        return redirect()->route('app.patients.show', ['clinic' => auth()->user()->clinic->slug, 'patient' => $this->patient->id]);
    }

    public function render()
    {
        return view('livewire.app.patients.edit', [
            'doctors' => $this->doctors,
        ])->layout('layouts.app');
    }
}
