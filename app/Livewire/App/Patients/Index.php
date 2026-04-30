<?php

namespace App\Livewire\App\Patients;

use App\Models\Clinic;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Clinic $currentClinic;

    public string $search = '';

    public string $status = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $listeners = [
        'patientCreated' => '$refresh',
        'patientUpdated' => '$refresh',
        'patientDeleted' => '$refresh',
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

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getPatientsProperty()
    {
        return Patient::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%')
                        ->orWhere('medical_record_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function deletePatient(string $id): void
    {
        $patient = Patient::findOrFail($id);

        if (! auth()->user()->can('patients.delete')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $patient->delete();

        session()->flash('success', __('patients.deleted_successfully'));
        $this->dispatch('patientDeleted');
    }

    public function toggleStatus(string $id): void
    {
        $patient = Patient::findOrFail($id);

        if (! auth()->user()->can('patients.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $patient->update(['is_active' => ! $patient->is_active]);

        session()->flash('success', __('patients.status_updated'));
    }

    public function render()
    {
        return view('livewire.app.patients.index', [
            'patients' => $this->patients,
        ])->layout('layouts.app');
    }

    /**
     * Build the filtered patient query (without pagination) — shared by exports.
     */
    private function buildExportQuery()
    {
        return Patient::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%')
                        ->orWhere('medical_record_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    private function activeFiltersText(): string
    {
        $parts = [];
        if ($this->search) {
            $parts[] = __('general.search').': "'.$this->search.'"';
        }
        if ($this->status !== '') {
            $parts[] = __('general.status').': '.($this->status === 'active' ? __('general.active') : __('general.inactive'));
        }

        return implode(' · ', $parts);
    }

    public function exportCsv()
    {
        abort_unless(auth()->user()->can('patients.export'), 403);

        $patients = $this->buildExportQuery()->get();
        $filtersText = $this->activeFiltersText();
        $filename = 'pacientes-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $clinic = $this->currentClinic;

        $callback = function () use ($patients, $filtersText, $clinic) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [__('patients.list_title').' — '.$clinic->name]);
            fputcsv($handle, [__('reports.generated_at'), now()->format('d/m/Y H:i')]);
            if ($filtersText) {
                fputcsv($handle, [__('reports.filters'), $filtersText]);
            }
            fputcsv($handle, [__('general.total'), $patients->count()]);
            fputcsv($handle, []);

            fputcsv($handle, [
                __('patients.medical_record_number'),
                __('patients.first_name'),
                __('patients.last_name'),
                __('patients.email'),
                __('patients.phone'),
                __('patients.birth_date'),
                __('patients.age'),
                __('patients.gender'),
                __('patients.blood_type'),
                __('general.status'),
                __('reports.generated_at'),
            ]);

            foreach ($patients as $p) {
                fputcsv($handle, [
                    $p->medical_record_number,
                    $p->first_name,
                    $p->last_name,
                    $p->email,
                    $p->phone,
                    $p->birth_date?->format('Y-m-d'),
                    $p->birth_date?->age,
                    $p->gender ? __('patients.'.$p->gender) : '',
                    $p->blood_type,
                    $p->is_active ? __('general.active') : __('general.inactive'),
                    optional($p->created_at)->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('patients.print'), 403);

        $patients = $this->buildExportQuery()->limit(500)->get();

        $pdf = Pdf::loadView('pdf.patients.list', [
            'clinic' => $this->currentClinic,
            'patients' => $patients,
            'filtersText' => $this->activeFiltersText(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'pacientes-'.now()->format('Ymd-His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
