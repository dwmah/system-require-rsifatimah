<?php

namespace App\Http\Controllers\Auth;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Services\Auth\ApplicantRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * Pendaftaran akun baru wajib terikat dengan posisi lowongan yang akan dilamar.
     */
    public function create(Request $request, ?JobVacancy $jobVacancy = null): View|RedirectResponse
    {
        $vacancyId = $jobVacancy?->id
            ?? $request->query('lowongan')
            ?? $request->query('vacancy')
            ?? $request->query('job_vacancy_id')
            ?? session('target_vacancy_id');

        if (! $vacancyId) {
            return redirect()->route('vacancies.index')
                ->with('info', 'Pendaftaran akun baru dilakukan saat memilih lowongan yang ingin dilamar. Silakan pilih lowongan pekerjaan terlebih dahulu.');
        }

        $targetVacancy = $jobVacancy && $jobVacancy->exists
            ? $jobVacancy->loadMissing('recruitmentPeriod')
            : JobVacancy::with('recruitmentPeriod')->find($vacancyId);

        if (! $targetVacancy || ! $targetVacancy->isCurrentlyOpen()) {
            return redirect()->route('vacancies.index')
                ->with('error', 'Lowongan pekerjaan yang Anda pilih sedang tidak dibuka atau masa pendaftaran telah berakhir.');
        }

        session(['target_vacancy_id' => $targetVacancy->id]);

        return view('auth.register', compact('targetVacancy'));
    }

    /**
     * Handle an incoming registration request.
     * Otomatis membuat draf lamaran pada menu Lamaran Saya untuk formasi yang dituju.
     */
    public function store(RegisterRequest $request, ApplicantRegistrationService $registrationService): RedirectResponse
    {
        $validated = $request->validated();

        $vacancyId = $validated['job_vacancy_id']
            ?? $request->input('job_vacancy_id')
            ?? session('target_vacancy_id');

        $targetVacancy = $vacancyId
            ? JobVacancy::find($vacancyId)
            : JobVacancy::currentlyOpen()->first();

        if (! $targetVacancy || ! $targetVacancy->isCurrentlyOpen()) {
            return redirect()->route('vacancies.index')
                ->with('error', 'Lowongan pekerjaan yang dituju sedang tidak dibuka untuk pendaftaran.');
        }

        $user = $registrationService->register($validated);

        // Otomatis membuat draf lamaran pekerjaan untuk akun baru
        $application = JobApplication::firstOrCreate(
            [
                'user_id' => $user->id,
                'recruitment_period_id' => $targetVacancy->recruitment_period_id,
            ],
            [
                'job_vacancy_id' => $targetVacancy->id,
                'status' => JobApplicationStatus::DRAFT,
                'application_number' => null,
                'submitted_at' => null,
                'locked_at' => null,
                'accepted_at' => null,
                'rejected_at' => null,
                'hired_at' => null,
            ]
        );

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->forget('target_vacancy_id');

        return redirect()->route('portal.applications.index')
            ->with('success', 'Akun berhasil dibuat! Lamaran untuk posisi '.$targetVacancy->title.' telah otomatis ditambahkan ke menu Lamaran Saya.');
    }
}
