<?php

namespace App\Http\Controllers\Portal;

use App\Exceptions\Recruitment\DuplicateApplicationException;
use App\Exceptions\Recruitment\InactiveVacancyException;
use App\Exceptions\Recruitment\IncompleteProfileException;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\JobApplicationService;
use App\Services\Recruitment\SelectionTrackingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Menampilkan daftar seluruh lamaran pekerjaan milik pelamar yang sedang aktif.
     */
    public function index(Request $request, SelectionTrackingService $trackingService): View
    {
        $applications = $request->user()
            ->jobApplications()
            ->with(['jobVacancy', 'recruitmentPeriod', 'snapshot', 'documents'])
            ->latest()
            ->get();

        foreach ($applications as $app) {
            $app->safe_status = $trackingService->publicApplicationStatus($app);
        }

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
            // Jika sudah dikirim / terkunci resmi: tidak boleh ganti lowongan
            if ($existing->isLocked() || ! $existing->isDraft()) {
                return redirect()->route('portal.applications.show', $existing)
                    ->with('info', 'Anda sudah memiliki lamaran yang telah diajukan pada periode rekrutmen ini.');
            }

            // Jika lowongan sama dengan draft yang ada
            if ($existing->job_vacancy_id === $jobVacancy->id) {
                return redirect()->route('portal.applications.show', $existing)
                    ->with('info', 'Anda sedang mengerjakan draft lamaran untuk formasi ini.');
            }

            // Jika lowongan berbeda dan masih berstatus DRAFT: pelamar boleh ganti formasi!
            $jobVacancy->loadMissing(['qualifications', 'recruitmentPeriod']);

            return view('portal.applications.start', [
                'jobVacancy' => $jobVacancy,
                'completenessPercentage' => 100,
                'existingDraft' => $existing,
            ]);
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
        JobApplicationService $service,
        ?JobVacancy $jobVacancy = null
    ): RedirectResponse {
        $targetVacancy = ($jobVacancy && $jobVacancy->exists)
            ? $jobVacancy
            : JobVacancy::findOrFail($request->input('job_vacancy_id'));

        $existing = JobApplication::where('user_id', $request->user()->id)
            ->where('recruitment_period_id', $targetVacancy->recruitment_period_id)
            ->first();

        if ($existing) {
            // Jika terdapat konfirmasi pergantian formasi dan draft belum dikunci
            if (($request->boolean('confirm_switch') || $request->boolean('switch_draft')) && $existing->isDraft() && ! $existing->isLocked()) {
                try {
                    $application = $service->switchVacancy($request->user(), $existing, $targetVacancy);

                    return redirect()->route('portal.applications.show', $application)
                        ->with('success', 'Formasi lamaran berhasil diganti ke "' . $targetVacancy->title . '". Dokumen persyaratan draft sebelumnya telah dibersihkan.');
                } catch (IncompleteProfileException $e) {
                    return redirect()->route('profile.show')
                        ->with('warning', 'Lengkapi profil dasar Anda sebelum mengajukan lamaran.');
                } catch (InactiveVacancyException $e) {
                    return redirect()->route('vacancies.index')
                        ->with('error', 'Lowongan ini sedang tidak dibuka untuk pendaftaran.');
                } catch (DomainException $e) {
                    return redirect()->back()
                        ->with('error', $e->getMessage());
                }
            }

            return redirect()->route('portal.applications.show', $existing)
                ->with('info', 'Anda sudah memiliki lamaran pada periode rekrutmen ini.');
        }

        try {
            $application = $service->startDraft($request->user(), $targetVacancy);

            if ($request->route('jobVacancy')) {
                return redirect()->route('portal.applications.show', $application)
                    ->with('success', 'Draft lamaran berhasil dibuat. Anda dapat mengganti formasi selama lamaran berstatus draft.');
            }

            return redirect()->route('portal.applications.index')
                ->with('success', 'Draft lamaran berhasil dibuat.');
        } catch (DuplicateApplicationException $e) {
            $existing = JobApplication::where('user_id', $request->user()->id)
                ->where('recruitment_period_id', $targetVacancy->recruitment_period_id)
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
     * Membatalkan draft lamaran pekerjaan yang sedang aktif.
     * Sesuai aturan: pelamar tanpa lamaran tidak boleh memiliki akun.
     * Jika setelah draft dibatalkan pelamar tidak memiliki lamaran lagi,
     * akun pengguna dihapus, sesi di-logout, dan diarahkan ke daftar lowongan.
     */
    public function cancelDraft(
        Request $request,
        JobApplication $jobApplication,
        JobApplicationService $service
    ): RedirectResponse {
        Gate::authorize('cancelDraft', $jobApplication);

        try {
            $user = $request->user();
            $service->cancelDraft($user, $jobApplication);

            if (! $user->exists || ! User::where('id', $user->id)->exists()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('vacancies.index')
                    ->with('success', 'Draft lamaran dan akun Anda telah berhasil dibatalkan.');
            }

            return redirect()->route('vacancies.index')
                ->with('success', 'Draft lamaran berhasil dibatalkan.');
        } catch (DomainException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mengganti formasi lowongan pada draft lamaran pekerjaan.
     */
    public function switchVacancy(
        Request $request,
        JobApplication $jobApplication,
        JobApplicationService $service
    ): RedirectResponse {
        Gate::authorize('changeVacancy', $jobApplication);

        $targetVacancyId = $request->input('job_vacancy_id');
        if (! $targetVacancyId) {
            return redirect()->back()->with('error', 'Pilih lowongan tujuan yang ingin dilamar.');
        }

        $targetVacancy = JobVacancy::findOrFail($targetVacancyId);

        try {
            $newApplication = $service->switchVacancy($request->user(), $jobApplication, $targetVacancy);

            return redirect()->route('portal.applications.show', $newApplication)
                ->with('success', 'Formasi lamaran berhasil diganti ke "' . $targetVacancy->title . '". Dokumen persyaratan draft sebelumnya telah dibersihkan.');
        } catch (IncompleteProfileException $e) {
            return redirect()->route('profile.show')
                ->with('warning', 'Lengkapi profil dasar Anda sebelum mengajukan lamaran.');
        } catch (InactiveVacancyException $e) {
            return redirect()->route('vacancies.index')
                ->with('error', 'Lowongan ini sedang tidak dibuka untuk pendaftaran.');
        } catch (DomainException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menampilkan detail lamaran pekerjaan pelamar (Step 1 Data & Formasi).
     */
    public function show(JobApplication $jobApplication, SelectionTrackingService $trackingService): View
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

        $publicStatus = $trackingService->publicApplicationStatus($jobApplication);

        // Ambil lowongan lain yang sedang terbuka pada periode yang sama untuk opsi ganti formasi
        $otherVacancies = $jobApplication->isDraft() && ! $jobApplication->isLocked()
            ? JobVacancy::currentlyOpen()
                ->where('recruitment_period_id', $jobApplication->recruitment_period_id)
                ->where('id', '!=', $jobApplication->job_vacancy_id)
                ->get()
            : collect();

        return view('portal.applications.show', [
            'jobApplication' => $jobApplication,
            'publicStatus' => $publicStatus,
            'otherVacancies' => $otherVacancies,
        ]);
    }
}
