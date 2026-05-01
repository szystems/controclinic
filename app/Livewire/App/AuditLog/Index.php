<?php

namespace App\Livewire\App\AuditLog;

use App\Models\Clinic;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use WithPagination;

    public Clinic $clinic;

    #[Url]
    public string $filterEvent = '';

    #[Url]
    public string $filterSubject = '';

    #[Url]
    public string $filterUser = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function mount(Clinic $clinic): void
    {
        $user = auth()->user();

        abort_unless($user->clinic_id === $clinic->id, 403);
        abort_unless($user->can('audit.view'), 403);

        $this->clinic = $clinic;
    }

    public function updatingFilterEvent(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSubject(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUser(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['filterEvent', 'filterSubject', 'filterUser', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function getActivitiesProperty()
    {
        return Activity::query()
            ->where('causer_type', User::class)
            ->whereHas('causer', fn ($q) => $q->where('clinic_id', $this->clinic->id))
            ->when($this->filterEvent !== '', fn ($q) => $q->where('description', $this->filterEvent))
            ->when($this->filterSubject !== '', fn ($q) => $q->where('subject_type', 'App\\Models\\'.$this->filterSubject))
            ->when($this->filterUser !== '', fn ($q) => $q->where('causer_id', $this->filterUser))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->with('causer', 'subject')
            ->latest()
            ->paginate(25);
    }

    public function getClinicUsersProperty()
    {
        return User::where('clinic_id', $this->clinic->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getHasFiltersProperty(): bool
    {
        return $this->filterEvent !== ''
            || $this->filterSubject !== ''
            || $this->filterUser !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }

    public function render()
    {
        return view('livewire.app.audit-log.index', [
            'activities' => $this->activities,
            'clinicUsers' => $this->clinicUsers,
            'hasFilters' => $this->hasFilters,
        ])->layout('layouts.app');
    }
}
