<?php

namespace App\Http\Controllers\Portal;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\VacancyDocumentRequirement;
use App\Services\Recruitment\ApplicationDocumentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDocumentController extends Controller
{
    /**
     * Menampilkan halaman kelola berkas persyaratan pelamar (Step 2).
     */
    public function index(
        JobApplication $jobApplication,
        ApplicationDocumentService $documentService
    ): View {
        Gate::authorize('view', $jobApplication);

        $jobApplication->loadMissing([
            'jobVacancy.documentRequirements',
            'jobVacancy.recruitmentPeriod',
            'documents.documentRequirement',
        ]);

        $requirements = $jobApplication->jobVacancy
            ->documentRequirements()
            ->orderByDesc('is_required')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $uploadedDocuments = $jobApplication->documents->keyBy('vacancy_document_requirement_id');
        $missingRequirements = $documentService->missingRequiredDocuments($jobApplication);

        return view('portal.applications.documents', [
            'jobApplication' => $jobApplication,
            'requirements' => $requirements,
            'uploadedDocuments' => $uploadedDocuments,
            'missingRequirements' => $missingRequirements,
            'documentService' => $documentService,
        ]);
    }

    /**
     * Mengunggah atau mengganti berkas persyaratan lamaran.
     */
    public function store(
        Request $request,
        JobApplication $jobApplication,
        ApplicationDocumentService $documentService,
        ?VacancyDocumentRequirement $requirement = null
    ): RedirectResponse {
        Gate::authorize('update', $jobApplication);

        $targetRequirement = ($requirement && $requirement->exists)
            ? $requirement
            : VacancyDocumentRequirement::findOrFail($request->input('vacancy_document_requirement_id'));

        // Verifikasi kepemilikan requirement terhadap lowongan lamaran
        if ($targetRequirement->job_vacancy_id !== $jobApplication->job_vacancy_id) {
            abort(404, 'Persyaratan berkas tidak sesuai dengan formasi lamaran.');
        }

        $file = $request->file('file') ?? $request->file('document_file');
        if (! $file) {
            return back()->withErrors(['file' => 'Pilih file yang ingin diunggah.']);
        }

        try {
            $documentService->uploadOrReplace(
                $jobApplication,
                $targetRequirement,
                $file
            );

            return redirect()->route('portal.applications.documents.index', $jobApplication)
                ->with('success', 'Berkas "' . $targetRequirement->name . '" berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengunggah berkas. Silakan coba lagi.');
        }
    }

    /**
     * Menghapus berkas persyaratan lamaran (hanya saat DRAFT dan unlocked).
     */
    public function destroy(
        JobApplication $jobApplication,
        ApplicationDocument $document,
        ApplicationDocumentService $documentService
    ): RedirectResponse {
        Gate::authorize('update', $jobApplication);

        // Verifikasi kepemilikan dokumen terhadap lamaran
        if ($document->job_application_id !== $jobApplication->id) {
            abort(404, 'Dokumen tidak ditemukan pada lamaran ini.');
        }

        try {
            $documentService->delete($jobApplication, $document);

            return redirect()->route('portal.applications.documents.index', $jobApplication)
                ->with('success', 'Berkas berhasil dihapus.');
        } catch (DomainException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus berkas.');
        }
    }

    /**
     * Mengunduh berkas fisik privat pelamar secara aman.
     */
    public function download(
        JobApplication $jobApplication,
        ApplicationDocument $document,
        ApplicationDocumentService $documentService
    ): StreamedResponse {
        Gate::authorize('downloadDocument', [$jobApplication, $document]);

        if ($document->job_application_id !== $jobApplication->id) {
            abort(404, 'Dokumen tidak sesuai dengan lamaran terkait.');
        }

        $disk = Storage::disk(ApplicationDocumentService::DISK_NAME);

        if (! $document->stored_path || ! $disk->exists($document->stored_path)) {
            abort(404, 'File fisik berkas tidak ditemukan di server.');
        }

        $safeFilename = $documentService->sanitizeOriginalFilename($document->original_name);

        return $disk->download(
            $document->stored_path,
            $safeFilename,
            [
                'Content-Type' => $document->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
            ]
        );
    }
}
