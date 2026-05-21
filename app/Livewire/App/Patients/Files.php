<?php

namespace App\Livewire\App\Patients;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Files extends Component
{
    use WithFileUploads;

    public Clinic $currentClinic;

    public Patient $patient;

    public array $uploads = [];

    public string $uploadCategory = 'other';

    public string $uploadName = '';

    public string $uploadNotes = '';

    public string $filterCategory = '';

    public bool $showUploader = false;

    // Para delete con confirmación
    public ?string $confirmDeleteId = null;

    public function mount(Clinic $clinic, Patient $patient): void
    {
        abort_unless($patient->clinic_id === $clinic->id, 404);
        $this->authorize('viewAny', PatientFile::class);

        $this->currentClinic = $clinic;
        $this->patient = $patient;
    }

    public function updatingUploads(): void
    {
        // auto-populate name from first file if empty
    }

    public function uploadFiles(): void
    {
        $this->authorize('create', PatientFile::class);

        $this->validate([
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => [
                'file',
                'max:20480', // 20 MB
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip',
            ],
            'uploadCategory' => ['required', 'in:'.implode(',', PatientFile::CATEGORIES)],
            'uploadName' => ['nullable', 'string', 'max:255'],
            'uploadNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($this->uploads as $upload) {
            $originalName = $upload->getClientOriginalName();
            $mime = $upload->getMimeType();
            $size = $upload->getSize();

            // Nombre descriptivo: usar el que el usuario puso, o el nombre original del archivo
            $displayName = $this->uploadName ?: pathinfo($originalName, PATHINFO_FILENAME);

            // Guardar con nombre aleatorio para no exponer el original
            $path = $upload->storeAs(
                "clinics/{$this->currentClinic->id}/patients/{$this->patient->id}/files",
                Str::uuid().'.'.$upload->extension(),
                'local'
            );

            PatientFile::create([
                'clinic_id' => $this->currentClinic->id,
                'patient_id' => $this->patient->id,
                'uploaded_by_user_id' => auth()->id(),
                'category' => $this->uploadCategory,
                'name' => $displayName,
                'original_filename' => $originalName,
                'disk_path' => $path,
                'disk' => 'local',
                'mime_type' => $mime,
                'size_bytes' => $size,
                'notes' => $this->uploadNotes ?: null,
            ]);
        }

        $this->reset(['uploads', 'uploadName', 'uploadNotes', 'uploadCategory', 'showUploader']);
        $this->uploadCategory = 'other';
        $this->dispatch('files-uploaded');
        session()->flash('success', __('files.uploaded_success'));
    }

    public function confirmDelete(string $fileId): void
    {
        $this->confirmDeleteId = $fileId;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function deleteFile(): void
    {
        $file = PatientFile::where('clinic_id', $this->currentClinic->id)
            ->findOrFail($this->confirmDeleteId);

        $this->authorize('delete', $file);

        // Eliminar archivo físico
        if (Storage::disk($file->disk)->exists($file->disk_path)) {
            Storage::disk($file->disk)->delete($file->disk_path);
        }

        $file->delete();

        $this->confirmDeleteId = null;
        session()->flash('success', __('files.deleted_success'));
    }

    public function render()
    {
        $totalFiles = $this->patient->files()->count();

        $files = $this->patient->files()
            ->with('uploadedBy')
            ->when($this->filterCategory, fn ($q) => $q->where('category', $this->filterCategory))
            ->latest()
            ->get();

        return view('livewire.app.patients.files', [
            'files' => $files,
            'totalFiles' => $totalFiles,
            'categories' => PatientFile::CATEGORIES,
        ]);
    }
}
