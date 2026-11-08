<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobApplicationStatus;
use App\Enums\JobVacancyStatus;
use App\Enums\RecruitmentPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStageHistory;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancySelectionStage;
use App\Services\Export\BiodataExcelExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminRecapController extends Controller
{
    /**
     * Tampilkan daftar rekap seluruh periode rekrutmen.
     */
    public function index(Request $request): View
    {
        $periods = RecruitmentPeriod::withCount([
            'jobVacancies as total_vacancies',
            'jobVacancies as open_vacancies_count' => function ($q) {
                $q->where('status', JobVacancyStatus::OPEN);
            },
            'jobApplications as total_applications',
            'jobApplications as passed_count' => function ($q) {
                $q->whereIn('status', [
                    JobApplicationStatus::PASSED_ADMINISTRATION,
                    JobApplicationStatus::IN_SELECTION,
                    JobApplicationStatus::ACCEPTED,
                ]);
            },
            'jobApplications as accepted_count' => function ($q) {
                $q->where('status', JobApplicationStatus::ACCEPTED);
            },
            'jobApplications as rejected_count' => function ($q) {
                $q->where('status', JobApplicationStatus::REJECTED);
            },
        ])
            ->latest('registration_start_at')
            ->paginate(10);

        return view('admin.recap.index', [
            'periods' => $periods,
        ]);
    }

    /**
     * Tampilkan detail rekap data untuk periode spesifik.
     */
    public function show(Request $request, RecruitmentPeriod $period): View
    {
        $vacancies = JobVacancy::where('recruitment_period_id', $period->id)
            ->withCount(['jobApplications', 'jobApplications as accepted_count' => function ($q) {
                $q->where('status', JobApplicationStatus::ACCEPTED);
            }])
            ->get();

        $query = JobApplication::where('recruitment_period_id', $period->id)
            ->with(['user.applicantProfile', 'jobVacancy', 'documents', 'currentStage']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('vacancy_id')) {
            $query->where('job_vacancy_id', $request->vacancy_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest('submitted_at')->paginate(20)->withQueryString();

        $metrics = [
            'total' => JobApplication::where('recruitment_period_id', $period->id)->count(),
            'accepted' => JobApplication::where('recruitment_period_id', $period->id)->where('status', JobApplicationStatus::ACCEPTED)->count(),
            'rejected' => JobApplication::where('recruitment_period_id', $period->id)->where('status', JobApplicationStatus::REJECTED)->count(),
            'in_selection' => JobApplication::where('recruitment_period_id', $period->id)->where('status', JobApplicationStatus::IN_SELECTION)->count(),
        ];

        return view('admin.recap.show', [
            'period' => $period,
            'vacancies' => $vacancies,
            'applications' => $applications,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Ekspor data rekap pelamar periode dalam format Excel.
     */
    public function export(Request $request, RecruitmentPeriod $period, BiodataExcelExportService $exportService): BinaryFileResponse
    {
        $vacancyId = $request->filled('vacancy_id') ? (int) $request->vacancy_id : null;
        $status = $request->query('status');
        $search = $request->query('search');

        return $exportService->exportApplicants(
            vacancyId: $vacancyId,
            status: $status,
            search: $search,
            recruitmentPeriodId: $period->id
        );
    }

    /**
     * Hapus rekapan periode rekrutmen beserta seluruh lowongan, berkas lamaran, dan file fisik dokumen.
     */
    public function destroy(RecruitmentPeriod $period): RedirectResponse
    {
        // Proteksi: Periode yang sedang aktif berjalan dilarang dihapus
        if ($period->status === RecruitmentPeriodStatus::OPEN) {
            return redirect()->back()->with('error', 'Periode rekrutmen yang sedang aktif (OPEN) tidak dapat dihapus.');
        }

        DB::transaction(function () use ($period) {
            // 1. Ambil seluruh ID lamaran pada periode ini
            $applicationIds = JobApplication::where('recruitment_period_id', $period->id)->pluck('id');

            // 2. Ambil seluruh dokumen fisik pelamar untuk dihapus dari storage disk
            $documents = ApplicationDocument::whereIn('job_application_id', $applicationIds)->get();
            foreach ($documents as $doc) {
                if ($doc->stored_path && Storage::disk('applicant_documents')->exists($doc->stored_path)) {
                    Storage::disk('applicant_documents')->delete($doc->stored_path);
                }
            }

            // 3. Hapus database dokumen dan riwayat tahapan lamaran
            ApplicationDocument::whereIn('job_application_id', $applicationIds)->delete();
            ApplicationStageHistory::whereIn('job_application_id', $applicationIds)->delete();
            JobApplication::where('recruitment_period_id', $period->id)->delete();

            // 4. Ambil seluruh lowongan pada periode ini
            $vacancyIds = JobVacancy::where('recruitment_period_id', $period->id)->pluck('id');

            // 5. Hapus persyaratan berkas dan tahapan seleksi lowongan
            VacancyDocumentRequirement::whereIn('job_vacancy_id', $vacancyIds)->delete();
            VacancySelectionStage::whereIn('job_vacancy_id', $vacancyIds)->delete();
            JobVacancy::where('recruitment_period_id', $period->id)->delete();

            // 6. Hapus master periode rekrutmen
            $period->delete();
        });

        return redirect()->route('admin.recap.index')
            ->with('success', "Rekapan data periode '{$period->name}' beserta seluruh berkas dokumen terkait berhasil dihapus.");
    }
}
