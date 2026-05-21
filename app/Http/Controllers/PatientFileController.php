<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\PatientFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientFileController extends Controller
{
    /**
     * Stream a patient file — never exposes the real storage path.
     * Validates clinic tenant scope + permission before serving.
     */
    public function show(Request $request, Clinic $clinic, string $file): StreamedResponse
    {
        $user = $request->user();

        $file = PatientFile::findOrFail($file);

        // Multi-tenant guard — archivo pertenece a la clínica del contexto
        abort_unless($file->clinic_id === $clinic->id, 404);
        abort_unless($user->clinic_id === $clinic->id, 403);

        // Permission guard
        abort_unless($user->can('files.view'), 403);

        abort_unless(Storage::disk($file->disk)->exists($file->disk_path), 404);

        $headers = [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="'.addslashes($file->original_filename).'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return Storage::disk($file->disk)->response($file->disk_path, $file->original_filename, $headers);
    }

    /**
     * Force-download a file (for non-previewable types).
     */
    public function download(Request $request, Clinic $clinic, string $file): StreamedResponse
    {
        $user = $request->user();

        $file = PatientFile::findOrFail($file);

        abort_unless($file->clinic_id === $clinic->id, 404);
        abort_unless($user->clinic_id === $clinic->id, 403);
        abort_unless($user->can('files.view'), 403);
        abort_unless(Storage::disk($file->disk)->exists($file->disk_path), 404);

        return Storage::disk($file->disk)->download($file->disk_path, $file->original_filename);
    }
}
