<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\VacancySelectionStage;
use App\Services\Export\BiodataExcelExportService;
use App\Services\Recruitment\ApplicationVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        $activePeriod = RecruitmentPeriod::where('status', RecruitmentPeriodStatus::OPEN)->first();

        // Scope otomatis ke periode aktif kecuali jika dispesifikasikan lain via query
        if ($request->filled('period_id')) {
            if ($request->period_id !== 'all') {
                $query->where('recruitment_period_id', $request->period_id);
            }
        } elseif ($activePeriod) {
            $query->where('recruitment_period_id', $activePeriod->id);
        }

        $applications = $query->latest('submitted_at')->paginate(15)->withQueryString();

        $vacanciesQuery = JobVacancy::orderBy('title');
        if ($request->filled('period_id') && $request->period_id !== 'all') {
            $vacanciesQuery->where('recruitment_period_id', $request->period_id);
        } elseif ($activePeriod) {
            $vacanciesQuery->where('recruitment_period_id', $activePeriod->id);
        }
        $vacancies = $vacanciesQuery->get();

        // Base metrics scoped to active period
        $baseMetricQuery = JobApplication::query();
        if ($request->filled('period_id') && $request->period_id !== 'all') {
            $baseMetricQuery->where('recruitment_period_id', $request->period_id);
        } elseif ($activePeriod) {
            $baseMetricQuery->where('recruitment_period_id', $activePeriod->id);
        }

        // Summary metrics
        $metrics = [
            'total' => (clone $baseMetricQuery)->count(),
            'pending' => (clone $baseMetricQuery)->where('status', JobApplicationStatus::SUBMITTED)->count(),
            'passed' => (clone $baseMetricQuery)->where('status', JobApplicationStatus::PASSED_ADMINISTRATION)->count(),
            'in_selection' => (clone $baseMetricQuery)->where('status', JobApplicationStatus::IN_SELECTION)->count(),
            'accepted' => (clone $baseMetricQuery)->where('status', JobApplicationStatus::ACCEPTED)->count(),
            'rejected' => (clone $baseMetricQuery)->where('status', JobApplicationStatus::REJECTED)->count(),
        ];

        return view('admin.applications.index', [
            'applications' => $applications,
            'vacancies' => $vacancies,
            'metrics' => $metrics,
            'activePeriod' => $activePeriod,
        ]);
    }

    /**
     * Unduh seluruh data biodata pelamar yang terdaftar dalam format Excel (.xlsx).
     */
    public function exportBiodata(Request $request, BiodataExcelExportService $exportService): BinaryFileResponse|RedirectResponse
    {
        $vacancyId = $request->filled('vacancy_id') ? (int) $request->vacancy_id : null;
        $status = $request->filled('status') ? $request->status : null;
        $search = $request->filled('search') ? $request->search : null;

        $hasApplications = JobApplication::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('application_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($vacancyId, fn ($q) => $q->where('job_vacancy_id', $vacancyId))
            ->when($status, fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', JobApplicationStatus::DRAFT))
            ->exists();

        if (! $hasApplications) {
            return back()->with('error', 'Belum ada data pelamar yang sesuai dengan kriteria untuk diekspor ke Excel.');
        }

        return $exportService->exportApplicants(
            vacancyId: $vacancyId,
            status: $status,
            search: $search
        );
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
     * Memvalidkan sekaligus seluruh berkas dokumen yang telah diunggah oleh pelamar.
     */
    public function verifyAllDocuments(JobApplication $application): RedirectResponse
    {
        $count = $this->verificationService->verifyAllDocuments($application, Auth::user());

        if ($count === 0) {
            return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'berkas']))
                ->with('warning', 'Pelamar belum mengunggah dokumen apa pun untuk divalidasi.');
        }

        $application->refresh();

        $message = "Seluruh berkas terunggah ({$count} berkas) berhasil divalidkan sekaligus.";
        if ($application->status === JobApplicationStatus::PASSED_ADMINISTRATION) {
            $message .= ' Pelamar otomatis dinyatakan lolos verifikasi berkas / administrasi.';
        }

        return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'berkas']))
            ->with('success', $message);
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
            'stage_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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
                $request->stage_note,
                $request->file('stage_attachment')
            );
        } catch (ValidationException $e) {
            $errorMessage = $e->errors()['quota'][0] ?? $e->errors()['stage_attachment'][0] ?? $e->getMessage();

            return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'tahapan']).'#tahapan-timeline')
                ->with('error', $errorMessage);
        }

        return redirect()->to(route('admin.applications.show', ['application' => $application, 'tab' => 'tahapan']).'#stage-item-'.$stage->id)
            ->with('success', "Tahapan seleksi '{$stage->stage_name}' berhasil diperbarui.");
    }

    /**
     * Download berkas lampiran hasil tes pada tahapan seleksi tertentu.
     */
    public function downloadStageAttachment(JobApplication $application, ApplicationStageHistory $history): StreamedResponse|BinaryFileResponse|RedirectResponse
    {
        if ((int) $history->job_application_id !== (int) $application->id) {
            abort(404, 'Data riwayat tahapan seleksi tidak sesuai dengan lamaran ini.');
        }

        if (! $history->attachment_path || ! Storage::disk('local')->exists($history->attachment_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan di penyimpanan server.');
        }

        $filename = $history->attachment_original_name ?: basename($history->attachment_path);
        $mime = $history->attachment_mime ?: 'application/octet-stream';

        if (request()->boolean('preview')) {
            return Storage::disk('local')->response($history->attachment_path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return Storage::disk('local')->download($history->attachment_path, $filename, [
            'Content-Type' => $mime,
        ]);
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
