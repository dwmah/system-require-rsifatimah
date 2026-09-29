<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\VacancySelectionStage;
use App\Services\Recruitment\ApplicationVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class AdminApplicationController extends Controller
{
    public function __construct(
        protected ApplicationVerificationService $verificationService
    ) {}

    /**
     * Tampilkan daftar seluruh lamaran yang masuk dengan multi-filter.
     */
    public function index(Request $request): View
    {
        $query = JobApplication::with([
            'user.applicantProfile',
            'jobVacancy',
            'recruitmentPeriod',
            'documents',
            'currentStage',
        ]);

        // Filter Keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Formasi Lowongan
        if ($request->filled('vacancy_id')) {
            $query->where('job_vacancy_id', $request->vacancy_id);
        }

        // Filter Status Lamaran
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest('submitted_at')->paginate(15)->withQueryString();
        $vacancies = JobVacancy::orderBy('title')->get();

        // Summary metrics
        $metrics = [
            'total' => JobApplication::count(),
            'pending' => JobApplication::where('status', JobApplicationStatus::SUBMITTED)->count(),
            'passed' => JobApplication::where('status', JobApplicationStatus::PASSED_ADMINISTRATION)->count(),
            'in_selection' => JobApplication::where('status', JobApplicationStatus::IN_SELECTION)->count(),
            'accepted' => JobApplication::where('status', JobApplicationStatus::ACCEPTED)->count(),
            'rejected' => JobApplication::where('status', JobApplicationStatus::REJECTED)->count(),
        ];

        return view('admin.applications.index', [
            'applications' => $applications,
            'vacancies' => $vacancies,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Halaman Utama Verifikasi Berkas & Profil Pelamar.
     */
    public function show(JobApplication $application): View
    {
        $application->load([
            'user.applicantProfile',
            'jobVacancy.documentRequirements',
            'jobVacancy.selectionStages',
            'jobVacancy.qualifications',
            'documents.documentRequirement',
            'stageHistories.selectionStage',
            'verifier',
            'currentStage',
        ]);

        $user = $application->user;

        // Load relasi biodata lengkap pelamar
        $educations = $user->applicantProfile ? $user->applicantProfile->educations()->orderBy('graduation_year', 'desc')->get() : collect();
        $licenses = $user->applicantProfile ? $user->applicantProfile->licenses()->get() : collect();
        $certifications = $user->applicantProfile ? $user->applicantProfile->certifications()->get() : collect();
        $workExperiences = $user->applicantProfile ? $user->applicantProfile->workExperiences()->orderBy('start_date', 'desc')->get() : collect();

        // Persyaratan dokumen dari lowongan terkait
        $requirements = $application->jobVacancy->documentRequirements;
        $uploadedDocuments = $application->documents->keyBy('vacancy_document_requirement_id');

        return view('admin.applications.show', [
            'application' => $application,
            'user' => $user,
            'profile' => $user->applicantProfile,
            'educations' => $educations,
            'licenses' => $licenses,
            'certifications' => $certifications,
            'workExperiences' => $workExperiences,
            'requirements' => $requirements,
            'uploadedDocuments' => $uploadedDocuments,
            'selectionStages' => $application->jobVacancy->selectionStages,
            'stageHistories' => $application->stageHistories->keyBy('vacancy_selection_stage_id'),
        ]);
    }

    /**
     * Verifikasi Administrasi (Approve / Reject lamaran).
     */
    public function verify(Request $request, JobApplication $application): RedirectResponse
    {
        $admin = Auth::user();

        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
            'admin_note' => 'nullable|string|max:500',
        ], [
            'action.required' => 'Tindakan verifikasi wajib dipilih.',
            'rejection_reason.required_if' => 'Catatan alasan penolakan wajib diisi bila berkas ditolak/tidak lengkap.',
        ]);

        $target = $request->input('target');

        $biodataStage = $application->jobVacancy->selectionStages()
            ->where(function ($q) {
                $q->where('name', 'like', '%Pendaftaran%')
                    ->orWhere('name', 'like', '%Biodata%')
                    ->orWhere('name', 'like', '%Portofolio%');
            })
            ->first();

        $isBiodataAction = $target === 'biodata' || ($biodataStage && (int) $application->current_stage_id === (int) $biodataStage->id);

        if ($request->action === 'approve') {
            if ($isBiodataAction) {
                $this->verificationService->approveBiodata($application, $admin, $request->admin_note);

                return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'profil']).'#biodata-panel')
                    ->with('success', "Verifikasi Biodata atas nama {$application->user->name} berhasil DISETUJUI (Lolos Biodata & Portofolio).");
            }

            $this->verificationService->approveAdministration($application, $admin, $request->admin_note);

            return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'profil']).'#biodata-panel')
                ->with('success', "Verifikasi Administrasi atas nama {$application->user->name} berhasil DISETUJUI (Lolos Seleksi Berkas).");
        } else {
            if ($isBiodataAction) {
                $this->verificationService->rejectBiodata($application, $admin, $request->rejection_reason);

                return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'profil']).'#biodata-panel')
                    ->with('info', "Verifikasi Biodata atas nama {$application->user->name} telah DITOLAK dengan alasan: {$request->rejection_reason}.");
            }

            $this->verificationService->rejectAdministration($application, $admin, $request->rejection_reason);

            return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'profil']).'#biodata-panel')
                ->with('info', "Verifikasi atas nama {$application->user->name} telah DITOLAK dengan alasan: {$request->rejection_reason}.");
        }
    }

    /**
     * Verifikasi status per item dokumen (Valid / Invalid).
     */
    public function verifyDocument(Request $request, ApplicationDocument $document): RedirectResponse
    {
        $request->validate([
            'verification_status' => 'required|in:valid,invalid,pending',
            'verification_note' => 'nullable|string|max:500',
        ]);

        $this->verificationService->verifyDocument(
            $document,
            $request->verification_status,
            $request->verification_note,
            Auth::user()
        );

        $document->jobApplication->refresh();

        return redirect()->to(route('admin.applications.show', ['application' => $document->jobApplication, 'tab' => 'berkas']).'#doc-row-'.$document->id)
            ->with('success', "Status dokumen '{$document->documentRequirement->document_name}' berhasil diperbarui ({$request->verification_status}).");
    }

    /**
     * Perbarui status tahapan seleksi lanjutan (Tes Tulis, Interview, MCU, Diterima).
     */
    public function updateStage(Request $request, JobApplication $application): RedirectResponse
    {
        $request->validate([
            'stage_id' => 'required|exists:vacancy_selection_stages,id',
            'status' => 'required|in:passed,rejected,failed,in_progress,pending',
            'stage_note' => 'nullable|string|max:1000',
        ]);

        $stage = VacancySelectionStage::findOrFail($request->stage_id);

        if ($stage->job_vacancy_id !== $application->job_vacancy_id) {
            return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'tahapan']).'#tahapan-timeline')
                ->with('error', 'Tahapan seleksi tidak valid untuk formasi ini.');
        }

        try {
            $this->verificationService->updateSelectionStage(
                $application,
                $stage,
                $request->status,
                $request->stage_note
            );
        } catch (ValidationException $e) {
            $errorMessage = $e->errors()['quota'][0] ?? $e->getMessage();

            return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'tahapan']).'#tahapan-timeline')
                ->with('error', $errorMessage);
        }

        return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'tahapan']).'#stage-item-'.$stage->id)
            ->with('success', "Tahapan seleksi '{$stage->stage_name}' berhasil diperbarui.");
    }

    /**
     * Download seluruh berkas dokumen lamaran pelamar dalam format ZIP.
     */
    public function downloadAllDocuments(JobApplication $application): BinaryFileResponse|RedirectResponse
    {
        $documents = $application->documents()->with('documentRequirement')->get();

        if ($documents->isEmpty()) {
            return back()->with('error', 'Tidak ada berkas dokumen yang terunggah untuk diunduh.');
        }

        $applicantName = $application->user ? Str::slug($application->user->name, '_') : 'pelamar';
        $regNumber = $application->user ? Str::slug($application->user->registration_number ?? $application->application_number ?? (string) $application->id, '_') : (string) $application->id;
        $zipFileName = "Berkas_{$applicantName}_{$regNumber}.zip";

        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempZipPath = $tempDir.'/'.uniqid('zip_bundle_').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file arsip ZIP.');
        }

        $addedCount = 0;
        $applicantDisk = Storage::disk('applicant_documents');
        $localDisk = Storage::disk('local');

        foreach ($documents as $index => $doc) {
            $realPath = null;
            if ($doc->stored_path) {
                if ($applicantDisk->exists($doc->stored_path)) {
                    $realPath = $applicantDisk->path($doc->stored_path);
                } elseif ($localDisk->exists($doc->stored_path)) {
                    $realPath = $localDisk->path($doc->stored_path);
                } elseif (file_exists($doc->stored_path)) {
                    $realPath = $doc->stored_path;
                }
            }

            if ($realPath && file_exists($realPath)) {
                $reqName = $doc->documentRequirement ? Str::slug($doc->documentRequirement->document_name, '_') : 'dokumen_'.($index + 1);
                $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION) ?: 'pdf';
                $entryName = sprintf('%02d_%s.%s', $index + 1, $reqName, $ext);
                $zip->addFile($realPath, $entryName);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            if (file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }

            return back()->with('error', 'Berkas dokumen fisik tidak ditemukan di penyimpanan server.');
        }

        return response()->download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
