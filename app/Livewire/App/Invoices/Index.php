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

    public string $filterOverdue = '';  // '' | 'yes'

    public string $filterPaymentMethod = '';  // '' | cash | card | transfer | insurance | other

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'filterOverdue' => ['except' => ''],
        'filterPaymentMethod' => ['except' => ''],
    ];

    public function mount(Clinic $clinic): void
    {
        abort_unless($clinic->billingEnabled(), 403);
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

    public function updatingFilterOverdue(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $this->authorize('invoices.view');

        $invoices = Invoice::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->with(['patient', 'doctor', 'payments' => fn ($q) => $q->orderByDesc('paid_at')->limit(1)])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('patient', fn ($p) => $p->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->doctorId, fn ($q) => $q->where('doctor_id', $this->doctorId))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('issued_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('issued_at', '<=', $this->dateTo))
            ->when($this->filterOverdue === 'yes', fn ($q) => $q
                ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL])
                ->whereNotNull('due_at')
                ->whereDate('due_at', '<', today())
            )
            ->when($this->filterPaymentMethod, fn ($q) => $q
                ->whereHas('payments', fn ($p) => $p->where('method', $this->filterPaymentMethod))
            )
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

        return view('livewire.app.invoices.index', compact('invoices', 'doctors', 'statuses'))
            ->layout('layouts.app');
    }
}
