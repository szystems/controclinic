<?php

namespace App\Livewire\App\Invoices;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Clinic $currentClinic;

    public string $search = '';

    public string $status = '';

    public string $doctorId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDoctorId(): void
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

    public function render(): View
    {
        $this->authorize('invoices.view');

        $invoices = Invoice::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->with(['patient', 'doctor'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('patient', fn ($p) => $p->where('full_name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->doctorId, fn ($q) => $q->where('doctor_id', $this->doctorId))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('issued_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('issued_at', '<=', $this->dateTo))
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        $doctors = User::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->orderBy('name')
            ->get();

        $statuses = [
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_PENDING,
            Invoice::STATUS_PARTIAL,
            Invoice::STATUS_PAID,
            Invoice::STATUS_REFUNDED,
            Invoice::STATUS_CANCELLED,
        ];

        return view('livewire.app.invoices.index', compact('invoices', 'doctors', 'statuses'));
    }
}
