<?php

namespace App\Livewire\App\Appointments;

use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Clinic $currentClinic;

    public string $search = '';

    public string $status = '';

    public string $doctorId = '';

    public string $dateFilter = '';

    public string $sortField = 'appointment_date';

    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'appointmentCreated' => '$refresh',
        'appointmentUpdated' => '$refresh',
        'appointmentDeleted' => '$refresh',
    ];

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
        $this->dateFilter = now()->toDateString();
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

    public function updatingDateFilter(): void
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

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'doctorId']);
        $this->dateFilter = now()->toDateString();
        $this->resetPage();
    }

    public function showToday(): void
    {
        $this->dateFilter = now()->toDateString();
        $this->resetPage();
    }

    public function getDoctorsProperty()
    {
        return User::where('clinic_id', $this->currentClinic->id)
            ->whereIn('role', ['doctor', 'owner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAppointmentsProperty()
    {
        return Appointment::query()
            ->forClinic($this->currentClinic->id)
            ->with(['patient', 'doctor'])
            ->when($this->search, function ($query) {
                $query->whereHas('patient', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->doctorId, function ($query) {
                $query->where('doctor_id', $this->doctorId);
            })
            ->when($this->dateFilter, function ($query) {
                $query->forDate($this->dateFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('start_time', 'asc')
            ->paginate(15);
    }

    // Workflow Actions
    public function confirmAppointment(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $appointment->confirm();
        session()->flash('success', __('appointments.appointment_confirmed'));
    }

    public function checkIn(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $appointment->canCheckIn() && $appointment->status !== Appointment::STATUS_SCHEDULED) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        // Si está scheduled, primero confirmar
        if ($appointment->status === Appointment::STATUS_SCHEDULED) {
            $appointment->confirm();
        }

        $appointment->checkIn();
        session()->flash('success', __('appointments.check_in').' ✓');
    }

    public function startConsultation(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $appointment->canStart()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $appointment->start();
        session()->flash('success', __('appointments.start_consultation').' ✓');
    }

    public function completeAppointment(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $appointment->canComplete()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $appointment->complete();
        session()->flash('success', __('appointments.appointment_updated'));
    }

    public function cancelAppointment(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (! auth()->user()->can('appointments.delete')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $appointment->isCancellable()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $appointment->cancel();
        session()->flash('success', __('appointments.appointment_cancelled'));
    }

    public function markNoShow(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $appointment->markAsNoShow();
        session()->flash('success', __('appointments.appointment_updated'));
    }

    public function sendEmailReminder(string $id): void
    {
        $appointment = Appointment::with('patient')->findOrFail($id);

        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $appointment->patient?->email) {
            session()->flash('error', __('appointments.reminder_no_email'));

            return;
        }

        SendAppointmentNotification::dispatch($appointment->id, SendAppointmentNotification::TYPE_REMINDER);

        session()->flash('success', __('appointments.reminder_sent'));
    }

    public function render()
    {
        return view('livewire.app.appointments.index', [
            'appointments' => $this->appointments,
            'doctors' => $this->doctors,
            'statuses' => [
                Appointment::STATUS_SCHEDULED => __('appointments.status_scheduled'),
                Appointment::STATUS_CONFIRMED => __('appointments.status_confirmed'),
                Appointment::STATUS_WAITING => __('appointments.status_waiting'),
                Appointment::STATUS_IN_PROGRESS => __('appointments.status_in_progress'),
                Appointment::STATUS_COMPLETED => __('appointments.status_completed'),
                Appointment::STATUS_CANCELLED => __('appointments.status_cancelled'),
                Appointment::STATUS_NO_SHOW => __('appointments.status_no_show'),
            ],
        ])->layout('layouts.app');
    }

    /**
     * Build the filtered appointment query (without pagination) — shared by exports.
     */
    private function buildExportQuery()
    {
        return Appointment::query()
            ->forClinic($this->currentClinic->id)
            ->with(['patient', 'doctor'])
            ->when($this->search, function ($query) {
                $query->whereHas('patient', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->doctorId, fn ($query) => $query->where('doctor_id', $this->doctorId))
            ->when($this->dateFilter, fn ($query) => $query->forDate($this->dateFilter))
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    private function activeFiltersText(): string
    {
        $parts = [];
        if ($this->search) {
            $parts[] = __('general.search').': "'.$this->search.'"';
        }
        if ($this->status) {
            $parts[] = __('appointments.status').': '.__('appointments.status_'.$this->status);
        }
        if ($this->doctorId) {
            $doctor = User::find($this->doctorId);
            if ($doctor) {
                $parts[] = __('appointments.doctor').': '.$doctor->name;
            }
        }
        if ($this->dateFilter) {
            $parts[] = __('appointments.date').': '.Carbon::parse($this->dateFilter)->format('d/m/Y');
        }

        return implode(' · ', $parts);
    }

    public function exportCsv()
    {
        abort_unless(auth()->user()->can('appointments.export'), 403);

        $appointments = $this->buildExportQuery()->get();
        $filtersText = $this->activeFiltersText();
        $filename = 'citas-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $clinic = $this->currentClinic;

        $callback = function () use ($appointments, $filtersText, $clinic) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [__('appointments.title').' — '.$clinic->name]);
            fputcsv($handle, [__('reports.generated_at'), now()->format('d/m/Y H:i')]);
            if ($filtersText) {
                fputcsv($handle, [__('reports.filters'), $filtersText]);
            }
            fputcsv($handle, [__('general.total'), $appointments->count()]);
            fputcsv($handle, []);

            fputcsv($handle, [
                __('appointments.date'),
                __('appointments.start_time'),
                __('appointments.end_time'),
                __('appointments.patient'),
                __('patients.medical_record_number'),
                __('patients.phone'),
                __('appointments.doctor'),
                __('appointments.type'),
                __('appointments.status'),
                __('appointments.room'),
                __('appointments.reason'),
            ]);

            foreach ($appointments as $a) {
                fputcsv($handle, [
                    $a->appointment_date?->format('Y-m-d'),
                    $a->start_time?->format('H:i'),
                    $a->end_time?->format('H:i'),
                    trim(($a->patient?->first_name ?? '').' '.($a->patient?->last_name ?? '')),
                    $a->patient?->medical_record_number,
                    $a->patient?->phone,
                    $a->doctor?->name,
                    $a->appointment_type ? __('appointments.'.$a->appointment_type) : '',
                    __('appointments.status_'.$a->status),
                    $a->room,
                    $a->reason,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('appointments.print'), 403);

        $appointments = $this->buildExportQuery()->limit(500)->get();

        $pdf = Pdf::loadView('pdf.appointments.list', [
            'clinic' => $this->currentClinic,
            'appointments' => $appointments,
            'filtersText' => $this->activeFiltersText(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'citas-'.now()->format('Ymd-His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
