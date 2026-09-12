<?php

namespace App\Http\Controllers\Portal;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Services\Notification\RecruitmentNotificationService;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicantApplicationController extends Controller
{
    public function __construct(
        protected RecruitmentService $recruitmentService,
        protected RecruitmentNotificationService $notificationService,
        protected ProfileCompletenessService $completenessService
    ) {
    }

    /**
     * Tampilkan halaman workspace lamaran pelamar.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $activePeriod = $this->recruitmentService->getActivePeriod();

        // Cari lamaran pelamar pada periode aktif
        $application = $user->jobApplications()
            ->with([
                'jobVacancy.documentRequirements',
                'jobVacancy.selectionStages',
                'documents.documentRequirement',
                'stageHistories.selectionStage',
                'recruitmentPeriod',
            ])
            ->latest()
            ->first();

        $openVacancies = $activePeriod
            ? $activePeriod->vacancies()->where('status', \App\Enums\JobVacancyStatus::OPEN)->get()
            : collect();

        $profileCompleteness = $this->completenessService->percentage($user);

        return view('portal.applications.workspace', [
            'user' => $user,
            'application' => $application,
            'activePeriod' => $activePeriod,
            'openVacancies' => $openVacancies,
            'profileCompleteness' => $profileCompleteness,
        ]);
    }

    /**
     * Form pemilihan formasi lowongan kerja.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $activePeriod = $this->recruitmentService->getActivePeriod();

        if (!$activePeriod || !$activePeriod->isOpenForSubmission()) {
            return redirect()->route('portal.applications.index')->with('error', 'Periode pendaftaran sedang ditutup atau belum dimulai.');
        }

        // Cek jika pelamar sudah memiliki lamaran pada periode aktif
        $existingApplication = $user->jobApplications()
            ->where('recruitment_period_id', $activePeriod->id)
            ->first();

        if ($existingApplication) {
            return redirect()->route('portal.applications.index')->with('info', 'Anda sudah memiliki pendaftaran formasi pada periode ini.');
        }

        $vacancies = $activePeriod->vacancies()
            ->where('status', \App\Enums\JobVacancyStatus::OPEN)
            ->with(['qualifications', 'documentRequirements'])
            ->get();

        $selectedVacancyId = $request->query('vacancy_id');

        return view('portal.applications.apply', [
            'user' => $user,
            'activePeriod' => $activePeriod,
            'vacancies' => $vacancies,
            'selectedVacancyId' => $selectedVacancyId,
        ]);
    }

    /**
     * Simpan pilihan formasi dan inisialisasi lamaran baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $activePeriod = $this->recruitmentService->getActivePeriod();

        if (!$activePeriod || !$activePeriod->isOpenForSubmission()) {
            return redirect()->route('portal.applications.index')->with('error', 'Pendaftaran sedang tidak dibuka.');
        }

        $request->validate([
            'job_vacancy_id' => 'required|exists:job_vacancies,id',
        ], [
            'job_vacancy_id.required' => 'Silakan pilih posisi formasi yang ingin dilamar.',
            'job_vacancy_id.exists' => 'Posisi formasi yang dipilih tidak valid.',
        ]);

        $vacancy = JobVacancy::findOrFail($request->job_vacancy_id);

        if ($vacancy->recruitment_period_id !== $activePeriod->id || $vacancy->status !== \App\Enums\JobVacancyStatus::OPEN) {
            return back()->with('error', 'Lowongan ini tidak tersedia untuk pendaftaran.');
        }

        // Cek apakah sudah pernah mendaftar di periode ini
        $existing = $user->jobApplications()
            ->where('recruitment_period_id', $activePeriod->id)
            ->first();

        if ($existing) {
            return redirect()->route('portal.applications.index')->with('warning', 'Anda sudah memiliki lamaran aktif.');
        }

        // Generate nomor pendaftaran unik
        $year = date('Y');
        $randomSeq = strtoupper(Str::random(4));
        $applicationNumber = "RSF-{$year}-{$vacancy->id}-{$randomSeq}";

        $application = JobApplication::create([
            'user_id' => $user->id,
            'recruitment_period_id' => $activePeriod->id,
            'job_vacancy_id' => $vacancy->id,
            'application_number' => $applicationNumber,
            'status' => JobApplicationStatus::DRAFT,
        ]);

        return redirect()->route('portal.applications.index')->with('success', "Formasi {$vacancy->title} berhasil dipilih! Silakan unggah dokumen persyaratan sebelum batas akhir submit.");
    }

    /**
     * Final Submit Lamaran Pelamar.
     * Mengunci dokumen dan mengirim berkas ke Tim SDI.
     */
    public function submit(JobApplication $application): RedirectResponse
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id) {
            abort(403);
        }

        if (!$application->isDraft()) {
            return redirect()->route('portal.applications.index')->with('info', 'Lamaran Anda sudah disubmit sebelumnya.');
        }

        $activePeriod = $application->recruitmentPeriod;
        if (!$activePeriod || !$activePeriod->isOpenForSubmission()) {
            return redirect()->route('portal.applications.index')->with('error', 'Batas waktu submit dokumen telah berakhir (Pendaftaran Terkunci).');
        }

        // Validasi kelengkapan dokumen wajib
        $mandatoryRequirements = $application->jobVacancy->documentRequirements()
            ->where('is_mandatory', true)
            ->get();

        $uploadedReqIds = $application->documents()->pluck('vacancy_document_requirement_id')->toArray();

        $missingDocs = [];
        foreach ($mandatoryRequirements as $req) {
            if (!in_array($req->id, $uploadedReqIds)) {
                $missingDocs[] = $req->document_name;
            }
        }

        if (!empty($missingDocs)) {
            $missingList = implode(', ', $missingDocs);
            return back()->with('error', "Dokumen wajib belum lengkap: {$missingList}. Mohon unggah seluruh berkas bertanda (*) sebelum mengirim lamaran.");
        }

        // Finalisasi submit
        $application->update([
            'status' => JobApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        // Kirim notifikasi dual-channel
        $this->notificationService->notifyApplicationSubmitted($application);

        return redirect()->route('portal.applications.index')->with('success', 'Lamaran Anda berhasil dikirimkan! Berkas sedang menunggu verifikasi oleh Tim SDI RSI Fatimah Banyuwangi.');
    }

    /**
     * Konfirmasi pelamar telah melihat pengumuman penolakan (Acknowledge).
     */
    public function acknowledgeAnnouncement(JobApplication $application): RedirectResponse
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id) {
            abort(403);
        }

        if ($application->isRejected() && is_null($application->announcement_seen_at)) {
            $application->update([
                'announcement_seen_at' => now(),
            ]);
        }

        return back()->with('info', 'Terima kasih telah mengonfirmasi status hasil pengumuman.');
    }
}
