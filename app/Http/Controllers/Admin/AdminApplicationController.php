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
use Illuminate\View\View;

class AdminApplicationController extends Controller
{
    public function __construct(
        protected ApplicationVerificationService $verificationService
    ) {
    }

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

        if ($request->action === 'approve') {
            $this->verificationService->approveAdministration($application, $admin, $request->admin_note);
            return back()->with('success', "Lamaran atas nama {$application->user->name} berhasil DISETUJUI (Lolos Administrasi). Notifikasi telah dikirim ke pelamar.");
        } else {
            $this->verificationService->rejectAdministration($application, $admin, $request->rejection_reason);
            return back()->with('info', "Lamaran atas nama {$application->user->name} telah DITOLAK dengan alasan: {$request->rejection_reason}. Notifikasi telah dikirim ke pelamar.");
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
            $request->verification_note
        );

        return back()->with('success', "Status dokumen '{$document->documentRequirement->document_name}' berhasil diperbarui.");
    }

    /**
     * Perbarui status tahapan seleksi lanjutan (Tes Tulis, Interview, MCU, Diterima).
     */
    public function updateStage(Request $request, JobApplication $application): RedirectResponse
    {
        $request->validate([
            'stage_id' => 'required|exists:vacancy_selection_stages,id',
            'status' => 'required|in:passed,rejected,in_progress,pending',
            'stage_note' => 'nullable|string|max:1000',
        ]);

        $stage = VacancySelectionStage::findOrFail($request->stage_id);

        if ($stage->job_vacancy_id !== $application->job_vacancy_id) {
            return back()->with('error', 'Tahapan seleksi tidak valid untuk formasi ini.');
        }

        $this->verificationService->updateSelectionStage(
            $application,
            $stage,
            $request->status,
            $request->stage_note
        );

        return back()->with('success', "Tahapan seleksi '{$stage->stage_name}' berhasil diperbarui.");
    }
}
