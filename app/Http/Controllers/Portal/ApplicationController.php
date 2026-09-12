<?php

namespace App\Http\Controllers\Portal;

use App\Exceptions\Recruitment\DuplicateApplicationException;
use App\Exceptions\Recruitment\InactiveVacancyException;
use App\Exceptions\Recruitment\IncompleteProfileException;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\JobApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Menampilkan daftar seluruh lamaran pekerjaan milik pelamar yang sedang aktif.
     */
    public function index(Request $request): View
    {
        $applications = $request->user()
            ->jobApplications()
            ->with(['jobVacancy', 'recruitmentPeriod'])
            ->latest()
            ->get();

        return view('portal.applications.index', compact('applications'));
    }

    /**
     * Halaman konfirmasi pemilihan formasi sebelum pembuatan draft lamaran.
     * Operasi GET ini murni untuk review dan TIDAK memodifikasi database.
     */
    public function start(
        Request $request,
        JobVacancy $jobVacancy,
        ProfileCompletenessService $completenessService
    ): View|RedirectResponse {
        // 1. Prasyarat Kelengkapan Profil Dasar 100%
        if (! $completenessService->isComplete($request->user())) {
            return redirect()->route('profile.show')
                ->with('warning', 'Lengkapi profil dasar Anda sebelum mengajukan lamaran.');
        }

        // 2. Prasyarat Status Keterbukaan Lowongan
        if (! $jobVacancy->isCurrentlyOpen()) {
            return redirect()->route('vacancies.index')
                ->with('error', 'Lowongan ini sedang tidak dibuka untuk pendaftaran.');
        }

        // 3. Aturan 1 Akun = 1 Formasi per Periode
        $existing = JobApplication::where('user_id', $request->user()->id)
            ->where('recruitment_period_id', $jobVacancy->recruitment_period_id)
            ->first();

        if ($existing) {
            return redirect()->route('portal.applications.show', $existing)
                ->with('info', 'Anda sudah memiliki lamaran pada periode rekrutmen ini.');
        }

        $jobVacancy->loadMissing(['qualifications', 'recruitmentPeriod']);

        return view('portal.applications.start', [
            'jobVacancy' => $jobVacancy,
            'completenessPercentage' => 100,
        ]);
    }

    /**
     * Membuat record draft lamaran baru untuk formasi lowongan yang dipilih.
     */
    public function store(
        Request $request,
        JobVacancy $jobVacancy,
        JobApplicationService $service
    ): RedirectResponse {
        try {
            $application = $service->startDraft($request->user(), $jobVacancy);

            return redirect()->route('portal.applications.show', $application)
                ->with('success', 'Draft lamaran berhasil dibuat. Formasi yang dipilih telah terkunci untuk periode ini.');
        } catch (DuplicateApplicationException $e) {
            $existing = JobApplication::where('user_id', $request->user()->id)
                ->where('recruitment_period_id', $jobVacancy->recruitment_period_id)
                ->first();

            if ($existing) {
                return redirect()->route('portal.applications.show', $existing)
                    ->with('info', 'Anda sudah memiliki lamaran pada periode rekrutmen ini.');
            }

            return redirect()->route('portal.applications.index')
                ->with('info', 'Anda sudah memiliki lamaran pada periode rekrutmen ini.');
        } catch (IncompleteProfileException $e) {
            return redirect()->route('profile.show')
                ->with('warning', 'Lengkapi profil dasar Anda sebelum mengajukan lamaran.');
        } catch (InactiveVacancyException $e) {
            return redirect()->route('vacancies.index')
                ->with('error', 'Lowongan ini sedang tidak dibuka untuk pendaftaran.');
        }
    }

    /**
     * Menampilkan detail lamaran pekerjaan pelamar (Step 1 Data & Formasi terkunci).
     */
    public function show(JobApplication $jobApplication): View
    {
        Gate::authorize('view', $jobApplication);

        $jobApplication->loadMissing([
            'jobVacancy.qualifications',
            'jobVacancy.documentRequirements',
            'jobVacancy.selectionStages',
            'recruitmentPeriod',
            'user.applicantProfile',
            'documents.documentRequirement',
            'snapshot',
        ]);

        return view('portal.applications.show', compact('jobApplication'));
    }
}
