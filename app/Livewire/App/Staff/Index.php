<?php

namespace App\Livewire\App\Staff;

use App\Mail\ClinicInvitationMail;
use App\Models\Clinic;
use App\Models\ClinicInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Clinic $currentClinic;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'staffCreated' => '$refresh',
        'staffUpdated' => '$refresh',
    ];

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getMembersProperty()
    {
        return User::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->where('role', '!=', 'owner')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function getPendingInvitationsProperty()
    {
        return ClinicInvitation::where('clinic_id', $this->currentClinic->id)
            ->pending()
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getDoctorCountProperty(): int
    {
        return $this->currentClinic->doctors()->count();
    }

    public function getStaffCountProperty(): int
    {
        return $this->currentClinic->staff()->count();
    }

    public function getPlanLimitsProperty(): array
    {
        return $this->currentClinic->getPlanLimits();
    }

    public function toggleStatus(int $id): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $member = User::where('clinic_id', $this->currentClinic->id)->findOrFail($id);

        if ($member->isOwner()) {
            session()->flash('error', __('staff.cannot_deactivate_owner'));

            return;
        }

        $member->update(['is_active' => ! $member->is_active]);

        $message = $member->is_active ? __('staff.activated') : __('staff.deactivated');
        session()->flash('success', $message);
    }

    public function deleteMember(int $id): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $member = User::where('clinic_id', $this->currentClinic->id)->findOrFail($id);

        if ($member->isOwner()) {
            session()->flash('error', __('staff.cannot_delete_owner'));

            return;
        }

        if ($member->id === auth()->id()) {
            session()->flash('error', __('staff.cannot_delete_self'));

            return;
        }

        $member->delete();

        session()->flash('success', __('staff.deleted_successfully'));
    }

    public function resendInvitation(string $id): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $invitation = ClinicInvitation::where('clinic_id', $this->currentClinic->id)
            ->findOrFail($id);

        if (! $invitation->isPending()) {
            return;
        }

        // Regenerate token and extend expiration
        $invitation->update([
            'token' => ClinicInvitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)
            ->locale($this->currentClinic->locale ?? config('app.locale'))
            ->send(new ClinicInvitationMail($invitation->fresh()));

        session()->flash('success', __('invitations.invitation_resent'));
    }

    public function cancelInvitation(string $id): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $invitation = ClinicInvitation::where('clinic_id', $this->currentClinic->id)
            ->findOrFail($id);

        if (! $invitation->isPending()) {
            return;
        }

        $invitation->update(['cancelled_at' => now()]);

        session()->flash('success', __('invitations.invitation_cancelled'));
    }

    public function render()
    {
        return view('livewire.app.staff.index', [
            'members' => $this->members,
            'pendingInvitations' => $this->pendingInvitations,
        ])->layout('layouts.app');
    }
}
