<?php

namespace App\Livewire\App\Staff;

use App\Mail\ClinicInvitationMail;
use App\Models\Clinic;
use App\Models\ClinicInvitation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

    // Editing pending invitation
    public ?string $editingInvitationId = null;

    public string $editInvitationName = '';

    public string $editInvitationEmail = '';

    public string $editInvitationRole = '';

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
            ->withCount([
                'appointments as appointments_count',
                'medicalRecords as records_count',
            ])
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
            ->orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END") // owner siempre primero
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
        // Cuenta owner + doctores (practitioners) porque el owner ocupa un slot de doctor en el plan.
        return $this->currentClinic->practitioners()->count();
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

    public function editInvitation(string $id): void
    {
        if (! auth()->user()->can('users.manage')) {
            return;
        }

        $invitation = ClinicInvitation::where('clinic_id', $this->currentClinic->id)
            ->findOrFail($id);

        if (! $invitation->isPending()) {
            return;
        }

        $this->editingInvitationId = $id;
        $this->editInvitationName = $invitation->name;
        $this->editInvitationEmail = $invitation->email;
        $this->editInvitationRole = $invitation->role;
    }

    public function cancelEditInvitation(): void
    {
        $this->editingInvitationId = null;
        $this->editInvitationName = '';
        $this->editInvitationEmail = '';
        $this->editInvitationRole = '';
    }

    public function saveInvitation(): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $invitation = ClinicInvitation::where('clinic_id', $this->currentClinic->id)
            ->findOrFail($this->editingInvitationId);

        if (! $invitation->isPending()) {
            $this->cancelEditInvitation();

            return;
        }

        $this->validate([
            'editInvitationName' => ['required', 'string', 'max:255'],
            'editInvitationEmail' => ['required', 'email', 'max:255'],
            'editInvitationRole' => ['required', 'in:doctor,assistant,secretary,receptionist'],
        ]);

        $emailChanged = strtolower($this->editInvitationEmail) !== strtolower($invitation->email);

        // If email changed, ensure it's not already used or duplicated
        if ($emailChanged) {
            $alreadyUser = User::where('clinic_id', $this->currentClinic->id)
                ->where('email', $this->editInvitationEmail)
                ->exists();

            if ($alreadyUser) {
                $this->addError('editInvitationEmail', __('invitations.email_already_registered'));

                return;
            }

            $duplicate = ClinicInvitation::where('clinic_id', $this->currentClinic->id)
                ->where('email', $this->editInvitationEmail)
                ->where('id', '!=', $invitation->id)
                ->pending()
                ->exists();

            if ($duplicate) {
                $this->addError('editInvitationEmail', __('invitations.duplicate_pending'));

                return;
            }
        }

        $updateData = [
            'name' => $this->editInvitationName,
            'email' => $this->editInvitationEmail,
            'role' => $this->editInvitationRole,
        ];

        // If email changed, regenerate token and extend expiration so the new address gets a valid link
        if ($emailChanged) {
            $updateData['token'] = ClinicInvitation::generateToken();
            $updateData['expires_at'] = now()->addDays(7);
        }

        $invitation->update($updateData);

        // Re-send the invitation email (either new address or same address with updated role/name)
        Mail::to($invitation->fresh()->email)
            ->locale($this->currentClinic->locale ?? config('app.locale'))
            ->send(new ClinicInvitationMail($invitation->fresh()));

        $this->cancelEditInvitation();
        session()->flash('success', __('invitations.invitation_updated'));
    }

    public function render()
    {
        return view('livewire.app.staff.index', [
            'members' => $this->members,
            'pendingInvitations' => $this->pendingInvitations,
        ])->layout('layouts.app');
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('users.print'), 403);

        $members = User::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter, fn ($q) => $q->where('role', $this->roleFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy($this->sortField, $this->sortDirection)
            ->limit(500)
            ->get();

        $pdf = Pdf::loadView('pdf.staff.list', [
            'clinic' => $this->currentClinic,
            'members' => $members,
            'filters' => [
                'search' => $this->search,
                'role' => $this->roleFilter,
                'status' => $this->statusFilter,
            ],
        ])->setPaper('a4', 'portrait');

        $filename = 'personal-'.now()->format('Ymd-His').'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
