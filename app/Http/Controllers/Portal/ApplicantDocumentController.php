<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\VacancyDocumentRequirement;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantDocumentController extends Controller
{
    /**
     * Upload / ganti dokumen persyaratan lamaran.
     */
    public function store(Request $request, JobApplication $application): RedirectResponse
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id) {
            abort(403);
        }

        if (! $application->isDraft()) {
            return back()->with('error', 'Lamaran telah dikirim, dokumen tidak dapat diubah lagi.');
        }

        $activePeriod = $application->recruitmentPeriod;
        if (! $activePeriod || ! $activePeriod->isOpenForSubmission()) {
            return back()->with('error', 'Periode upload dokumen telah ditutup.');
        }

        $request->validate([
            'vacancy_document_requirement_id' => 'required|exists:vacancy_document_requirements,id',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,mp4|max:51200',
        ], [
            'vacancy_document_requirement_id.required' => 'Jenis persyaratan dokumen tidak valid.',
            'document_file.required' => 'Silakan pilih berkas dokumen yang ingin diunggah.',
            'document_file.mimes' => 'Format berkas hanya diperbolehkan PDF, JPG, JPEG, PNG, atau MP4 (Video).',
            'document_file.max' => 'Ukuran berkas dokumen melebihi batas maksimal yang diizinkan.',
        ]);

        $req = VacancyDocumentRequirement::findOrFail($request->vacancy_document_requirement_id);

        if ($req->job_vacancy_id !== $application->job_vacancy_id) {
            return back()->with('error', 'Persyaratan dokumen ini bukan milik formasi yang Anda lamar.');
        }

        $file = $request->file('document_file');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Cari dokumen lama jika pernah diunggah untuk dihapus filenya
        $existingDoc = $application->documents()
            ->where('vacancy_document_requirement_id', $req->id)
            ->first();

        if ($existingDoc && $existingDoc->stored_path && Storage::exists($existingDoc->stored_path)) {
            Storage::delete($existingDoc->stored_path);
        }

        // Simpan berkas di direktori privat
        $storedPath = $file->store("recruitment_documents/applicant_{$user->id}");

        ApplicationDocument::updateOrCreate(
            [
                'job_application_id' => $application->id,
                'vacancy_document_requirement_id' => $req->id,
            ],
            [
                'original_name' => $originalName,
                'stored_path' => $storedPath,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'verification_status' => 'pending',
                'verification_note' => null,
            ]
        );

        return back()->with('success', "Dokumen '{$req->document_name}' berhasil diunggah.");
    }

    /**
     * Hapus dokumen lamaran (hanya jika lamaran masih draft).
     */
    public function destroy(JobApplication $application, ApplicationDocument $document): RedirectResponse
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id || $document->job_application_id !== $application->id) {
            abort(403);
        }

        if (! $application->isDraft()) {
            return back()->with('error', 'Lamaran telah dikirim, dokumen tidak dapat dihapus.');
        }

        if ($document->stored_path && Storage::exists($document->stored_path)) {
            Storage::delete($document->stored_path);
        }

        $docName = $document->documentRequirement->document_name ?? 'Dokumen';
        $document->delete();

        return back()->with('info', "Dokumen '{$docName}' berhasil dihapus.");
    }

    /**
     * Cari disk dan path penyimpanan berkas dokumen yang valid.
     *
     * @return array{Filesystem, string}
     */
    protected function getDocumentDiskAndPath(ApplicationDocument $document): array
    {
        if (! $document->stored_path) {
            abort(404, 'Berkas dokumen tidak memiliki path penyimpanan.');
        }

        if (Storage::disk('applicant_documents')->exists($document->stored_path)) {
            return [Storage::disk('applicant_documents'), $document->stored_path];
        }

        if (Storage::disk('local')->exists($document->stored_path)) {
            return [Storage::disk('local'), $document->stored_path];
        }

        abort(404, 'Berkas dokumen fisik tidak ditemukan di penyimpanan server.');
    }

    /**
     * Preview berkas dokumen secara langsung (inline) di browser tanpa unduh.
     */
    public function preview(ApplicationDocument $document): StreamedResponse
    {
        $user = Auth::user();
        $application = $document->jobApplication;

        if (! $user) {
            abort(403);
        }

        // Cek hak akses: Pemilik dokumen ATAU Admin HRD
        if ($user->id !== $application->user_id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        [$disk, $path] = $this->getDocumentDiskAndPath($document);

        return $disk->response($path, $document->original_name, [
            'Content-Type' => $document->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
        ]);
    }

    /**
     * Download berkas dokumen secara aman.
     * Hanya pemilik dokumen atau Admin HRD yang dapat mengakses.
     */
    public function download(ApplicationDocument $document): StreamedResponse
    {
        $user = Auth::user();
        $application = $document->jobApplication;

        if (! $user) {
            abort(403);
        }

        // Cek hak akses: Pemilik dokumen ATAU Admin HRD
        if ($user->id !== $application->user_id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        [$disk, $path] = $this->getDocumentDiskAndPath($document);

        return $disk->download($path, $document->original_name, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
        ]);
    }
}
