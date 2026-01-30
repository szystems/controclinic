<?php

namespace App\Livewire\App\Patients;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Livewire\Component;

class Create extends Component
{
    public Clinic $currentClinic;

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

    protected function messages(): array
    {
        return [
            'first_name.required' => __('validation.required', ['attribute' => __('patients.first_name')]),
            'last_name.required' => __('validation.required', ['attribute' => __('patients.last_name')]),
            'phone.required' => __('validation.required', ['attribute' => __('patients.phone')]),
            'email.email' => __('validation.email', ['attribute' => __('patients.email')]),
            'birth_date.before' => __('validation.before', ['attribute' => __('patients.birth_date'), 'date' => __('general.today')]),
        ];
    }

    public function mount(Clinic $clinic)
    {
        $this->currentClinic = $clinic;
    }

    public function getDoctorsProperty()
    {
        return User::where('clinic_id', $this->currentClinic->id)
            ->whereIn('role', ['doctor', 'owner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        if (!auth()->user()->can('patients.create')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        $this->validate();

        // Check clinic limits
        if (!$this->currentClinic->canAddPatient()) {
            session()->flash('error', __('patients.limit_reached'));
            return;
        }

        $patient = Patient::create([
            'clinic_id' => $this->currentClinic->id,
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
                ]
            ] : null,
            'insurance_info' => $this->insurance_provider ? [
                'provider' => $this->insurance_provider,
                'policy_number' => $this->insurance_policy_number,
            ] : null,
            'notes' => $this->notes ?: null,
        ]);

        // Generate medical record number
        $patient->update([
            'medical_record_number' => $patient->generateMedicalRecordNumber(),
        ]);

        session()->flash('success', __('patients.created_successfully'));

        $this->dispatch('patientCreated');

        return redirect()->route('app.patients.index', ['clinic' => $this->currentClinic->slug]);
    }

    public function render()
    {
        return view('livewire.app.patients.create', [
            'doctors' => $this->doctors,
        ])->layout('layouts.app');
    }
}
