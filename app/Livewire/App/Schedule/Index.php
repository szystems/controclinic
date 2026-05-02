<?php

namespace App\Livewire\App\Schedule;

use App\Models\Clinic;
use App\Models\DoctorUnavailability;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public Clinic $clinic;

    // Form state
    public bool $showForm = false;

    public ?string $editingId = null;

    public string $date_from = '';

    public string $date_to = '';

    public bool $all_day = true;

    public string $time_from = '08:00';

    public string $time_to = '17:00';

    public string $reason = '';

    // Owner/admin: manage another doctor's schedule
    public ?int $selectedDoctorId = null;

    public function mount(Clinic $clinic): void
    {
        $user = Auth::user();
        abort_unless($user->clinic_id === $clinic->id, 403);
        abort_unless($user->can('schedule.manage'), 403);

        $this->clinic = $clinic;

        // Doctors and admins default to their own schedule
        if ($user->hasAnyRole(['doctor', 'assistant'])) {
            $this->selectedDoctorId = $user->id;
        }

        // Owner/admin can pick any doctor; default to first one
        if ($this->selectedDoctorId === null && $this->clinic->practitioners()->exists()) {
            $this->selectedDoctorId = $this->clinic->practitioners()->value('id');
        }
    }

    // ==================== COMPUTED ====================

    public function getTargetDoctorProperty(): ?User
    {
        if (! $this->selectedDoctorId) {
            return null;
        }

        return User::find($this->selectedDoctorId);
    }

    public function getUnavailabilitiesProperty()
    {
        if (! $this->selectedDoctorId) {
            return collect();
        }

        return DoctorUnavailability::query()
            ->forClinic($this->clinic->id)
            ->forDoctor($this->selectedDoctorId)
            ->where('date_to', '>=', today()->toDateString())
            ->orderBy('date_from')
            ->get();
    }

    public function getPastUnavailabilitiesProperty()
    {
        if (! $this->selectedDoctorId) {
            return collect();
        }

        return DoctorUnavailability::query()
            ->forClinic($this->clinic->id)
            ->forDoctor($this->selectedDoctorId)
            ->where('date_to', '<', today()->toDateString())
            ->orderByDesc('date_from')
            ->limit(10)
            ->get();
    }

    public function getCanManageOthersProperty(): bool
    {
        return Auth::user()->hasAnyRole(['owner', 'admin']);
    }

    public function getDoctorsProperty()
    {
        return $this->clinic->practitioners()->orderBy('name')->get(['id', 'name']);
    }

    // ==================== ACTIONS ====================

    public function openCreate(): void
    {
        $this->resetForm();
        $this->date_from = today()->toDateString();
        $this->date_to = today()->toDateString();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(string $id): void
    {
        $block = $this->findBlockForCurrentDoctor($id);
        if (! $block) {
            return;
        }

        $this->editingId = $id;
        $this->date_from = $block->date_from->toDateString();
        $this->date_to = $block->date_to->toDateString();
        $this->all_day = $block->all_day;
        $this->time_from = $block->time_from ?? '08:00';
        $this->time_to = $block->time_to ?? '17:00';
        $this->reason = $block->reason ?? '';
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $data = [
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->selectedDoctorId,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'all_day' => $this->all_day,
            'time_from' => $this->all_day ? null : $this->time_from,
            'time_to' => $this->all_day ? null : $this->time_to,
            'reason' => $this->reason ?: null,
            'created_by' => Auth::id(),
        ];

        if ($this->editingId) {
            $block = $this->findBlockForCurrentDoctor($this->editingId);
            if (! $block) {
                return;
            }
            $block->update($data);
            session()->flash('success', __('schedule.updated'));
        } else {
            DoctorUnavailability::create($data);
            session()->flash('success', __('schedule.created'));
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(string $id): void
    {
        $block = $this->findBlockForCurrentDoctor($id);
        if (! $block) {
            return;
        }

        $block->delete();
        session()->flash('success', __('schedule.deleted'));
    }

    public function updatedSelectedDoctorId(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->resetForm();
    }

    // ==================== HELPERS ====================

    private function findBlockForCurrentDoctor(string $id): ?DoctorUnavailability
    {
        return DoctorUnavailability::query()
            ->forClinic($this->clinic->id)
            ->forDoctor($this->selectedDoctorId ?? 0)
            ->where('id', $id)
            ->first();
    }

    private function resetForm(): void
    {
        $this->date_from = '';
        $this->date_to = '';
        $this->all_day = true;
        $this->time_from = '08:00';
        $this->time_to = '17:00';
        $this->reason = '';
        $this->editingId = null;
        $this->resetValidation();
    }

    private function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'all_day' => ['boolean'],
            'time_from' => $this->all_day ? [] : ['required', 'date_format:H:i'],
            'time_to' => $this->all_day ? [] : ['required', 'date_format:H:i', 'after:time_from'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function render()
    {
        return view('livewire.app.schedule.index', [
            'unavailabilities' => $this->unavailabilities,
            'pastUnavailabilities' => $this->pastUnavailabilities,
            'targetDoctor' => $this->targetDoctor,
            'doctors' => $this->doctors,
            'canManageOthers' => $this->canManageOthers,
        ])->layout('layouts.app');
    }
}
