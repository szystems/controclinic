<?php

namespace App\Livewire\App\Patients;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Tag;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class Show extends Component
{
    public Patient $patient;

    public string $tab = 'datos';

    public bool $showDeleteModal = false;

    public bool $showTagPanel = false;

    public string $newTagName = '';

    public string $newTagColor = 'blue';

    protected $queryString = [
        'tab' => ['except' => 'datos', 'as' => 'tab'],
    ];

    /** Tabs disponibles y sus permisos requeridos (null = sin restricción) */
    public const TABS = [
        'datos'       => null,
        'citas'       => 'appointments.view',
        'historial'   => 'records.view',
        'recetas'     => 'prescriptions.view',
        'archivos'    => null,
        'facturacion' => 'invoices.view',
        'notas'       => 'patients.edit',
        'actividad'   => 'patients.view',
    ];

    public function mount(Patient $patient): void
    {
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);

        // Validar tab permitido
        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'datos';
        }

        $this->patient = $patient->load(['primaryDoctor', 'tags']);
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, self::TABS)) {
            $this->tab = $tab;
        }
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deletePatient()
    {
        if (! auth()->user()->can('patients.delete')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->patient->delete();

        session()->flash('success', __('patients.deleted_successfully'));

        return redirect()->route('app.patients.index', ['clinic' => auth()->user()->clinic->slug]);
    }

    public function toggleStatus(): void
    {
        if (! auth()->user()->can('patients.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->patient->update(['is_active' => ! $this->patient->is_active]);
        $this->patient->refresh();

        session()->flash('success', __('patients.status_updated'));
    }

    // ==================== TAG METHODS ====================

    public function getClinicTagsProperty()
    {
        return Tag::forClinic($this->patient->clinic_id)
            ->forPatients()
            ->orderBy('name')
            ->get();
    }

    public function getAssignedTagIdsProperty(): array
    {
        return $this->patient->tags->pluck('id')->toArray();
    }

    public function toggleTag(int $tagId): void
    {
        if (! auth()->user()->can('tags.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $tag = Tag::where('id', $tagId)
            ->where('clinic_id', $this->patient->clinic_id)
            ->firstOrFail();

        if ($this->patient->tags()->where('tags.id', $tagId)->exists()) {
            $this->patient->tags()->detach($tagId);
            $this->patient->load('tags');
            session()->flash('success', __('patients.tag_removed'));
        } else {
            $this->patient->tags()->attach($tagId, [
                'tagged_by' => auth()->id(),
                'tagged_at' => now(),
            ]);
            $this->patient->load('tags');
            session()->flash('success', __('patients.tag_added'));
        }
    }

    public function createAndAssignTag(): void
    {
        if (! auth()->user()->can('tags.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->validate([
            'newTagName' => ['required', 'string', 'max:50'],
            'newTagColor' => ['required', 'string', 'in:'.implode(',', Tag::COLORS)],
        ]);

        $tag = Tag::firstOrCreate(
            ['clinic_id' => $this->patient->clinic_id, 'name' => trim($this->newTagName)],
            ['color' => $this->newTagColor, 'category' => Tag::CATEGORY_PATIENT]
        );

        if (! $this->patient->tags()->where('tags.id', $tag->id)->exists()) {
            $this->patient->tags()->attach($tag->id, [
                'tagged_by' => auth()->id(),
                'tagged_at' => now(),
            ]);
        }

        $this->patient->load('tags');
        $this->newTagName = '';
        $this->newTagColor = 'blue';

        session()->flash('success', __('patients.tag_added'));
    }

    public function getUpcomingAppointmentsProperty()
    {
        return $this->patient->appointments()
            ->with('doctor')
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    public function getAllAppointmentsProperty()
    {
        if ($this->tab !== 'citas') {
            return null;
        }

        return $this->patient->appointments()
            ->with('doctor', 'invoice')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);
    }

    public function getRecentRecordsProperty()
    {
        return $this->patient->medicalRecords()
            ->with('doctor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getAllRecordsProperty()
    {
        if ($this->tab !== 'historial') {
            return null;
        }

        return $this->patient->medicalRecords()
            ->with('doctor')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getInvoicesProperty()
    {
        if ($this->tab !== 'facturacion') {
            return null;
        }

        return Invoice::where('patient_id', $this->patient->id)
            ->where('clinic_id', $this->patient->clinic_id)
            ->with('doctor')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getPrescriptionsProperty()
    {
        if ($this->tab !== 'recetas') {
            return null;
        }

        return Prescription::where('patient_id', $this->patient->id)
            ->where('clinic_id', $this->patient->clinic_id)
            ->with('doctor')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function getActivityLogsProperty()
    {
        if ($this->tab !== 'actividad') {
            return null;
        }

        return \Spatie\Activitylog\Models\Activity::where('subject_type', Patient::class)
            ->where('subject_id', $this->patient->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('patients.print'), 403);

        $appointments = $this->patient->appointments()
            ->with('doctor')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(20)
            ->get();

        $pdf = Pdf::loadView('pdf.patients.show', [
            'clinic' => $this->patient->clinic,
            'patient' => $this->patient,
            'appointments' => $appointments,
        ])->setPaper('a4', 'portrait');

        $filename = 'paciente-'.preg_replace('/[^A-Za-z0-9_-]/', '_', strtolower(trim($this->patient->first_name.'-'.$this->patient->last_name))).'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        return view('livewire.app.patients.show', [
            'upcomingAppointments' => $this->upcomingAppointments,
            'recentRecords'        => $this->recentRecords,
            'allAppointments'      => $this->allAppointments,
            'allRecords'           => $this->allRecords,
            'invoices'             => $this->invoices,
            'prescriptions'        => $this->prescriptions,
            'activityLogs'         => $this->activityLogs,
            'clinicTags'           => $this->clinicTags,
            'assignedTagIds'       => $this->assignedTagIds,
            'tagColors'            => Tag::COLORS,
        ])->layout('layouts.app');
    }
}
