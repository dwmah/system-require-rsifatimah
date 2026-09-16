<?php

namespace App\Http\Controllers\Portal;

use App\Enums\JobApplicationStatus;
use App\Exceptions\Recruitment\DocumentIntegrityException;
use App\Exceptions\Recruitment\InactiveVacancyException;
use App\Exceptions\Recruitment\IncompleteProfileException;
use App\Exceptions\Recruitment\MissingRequiredDocumentsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\SubmitApplicationRequest;
use App\Models\JobApplication;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Recruitment\ApplicationDocumentService;
use App\Services\Recruitment\ApplicationSubmissionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApplicationSubmissionController extends Controller
{
    /**
     * Halaman Review & Konfirmasi Final Lamaran (Step 3).
     * Hanya dapat diakses saat status DRAFT (menggunakan Live Profile).
     * Jika sudah SUBMITTED ke atas, dialihkan ke halaman detail resmi (Snapshot).
     */
    public function review(
        JobApplication $jobApplication,
        ApplicationDocumentService $documentService,
        ProfileCompletenessService $completenessService
    ): View|RedirectResponse {
        Gate::authorize('view', $jobApplication);

        // Jika sudah dikirim atau bukan draft, alihkan ke detail resmi
        if ($jobApplication->status !== JobApplicationStatus::DRAFT) {
            return redirect()->route('portal.applications.show', $jobApplication);
        }

        $jobApplication->loadMissing([
            'jobVacancy.qualifications',
            'jobVacancy.documentRequirements',
            'jobVacancy.selectionStages',
            'recruitmentPeriod',
            'documents.documentRequirement',
        ]);

        $user = $jobApplication->user;
        $user->loadMissing([
            'applicantProfile.educations',
            'applicantProfile.licenses',
            'applicantProfile.certifications',
            'applicantProfile.workExperiences',
        ]);

        $missingRequirements = $documentService->missingRequiredDocuments($jobApplication);
        $isProfileComplete = $completenessService->isComplete($user);
        $isVacancyOpen = $jobApplication->jobVacancy->isCurrentlyOpen();

        $profile = $user->applicantProfile;

        return view('portal.applications.review', [
            'jobApplication' => $jobApplication,
            'user' => $user,
            'profile' => $profile,
            'educations' => $profile?->educations ?? collect(),
            'licenses' => $profile?->licenses ?? collect(),
            'certifications' => $profile?->certifications ?? collect(),
            'workExperiences' => $profile?->workExperiences ?? collect(),
            'missingRequirements' => $missingRequirements,
            'isProfileComplete' => $isProfileComplete,
            'isVacancyOpen' => $isVacancyOpen,
        ]);
    }

    /**
     * Memproses Pengiriman Resmi Final Lamaran Pekerjaan.
     */
    public function submit(
        SubmitApplicationRequest $request,
        JobApplication $jobApplication,
        ApplicationSubmissionService $submissionService
    ): RedirectResponse {
        // Otorisasi controller: pelamar hanya dapat mengirim lamaran miliknya
        Gate::authorize('view', $jobApplication);

        if ($jobApplication->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak akses terhadap lamaran ini.');
        }

        try {
            $submitted = $submissionService->submit($request->user(), $jobApplication);

            return redirect()->route('portal.applications.show', $submitted)
                ->with('success', "Lamaran Anda dengan nomor {$submitted->application_number} telah berhasil dikirim. Berkas dan data profil telah terkunci.");
        } catch (IncompleteProfileException $e) {
            return redirect()->route('portal.applications.review', $jobApplication)
                ->with('error', 'Profil Anda belum 100% lengkap. Harap lengkapi profil terlebih dahulu.');
        } catch (InactiveVacancyException $e) {
            return redirect()->route('portal.applications.review', $jobApplication)
                ->with('error', 'Pendaftaran untuk formasi ini telah ditutup.');
        } catch (MissingRequiredDocumentsException $e) {
            $docs = $e->missingDocuments->join(', ');

            return redirect()->route('portal.applications.documents.index', $jobApplication)
                ->with('error', "Dokumen persyaratan wajib belum lengkap: {$docs}.");
        } catch (DocumentIntegrityException $e) {
            return redirect()->route('portal.applications.documents.index', $jobApplication)
                ->with('error', "Terjadi kendala pada berkas: {$e->getMessage()}");
        } catch (DomainException $e) {
            return redirect()->route('portal.applications.review', $jobApplication)
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('portal.applications.review', $jobApplication)
                ->with('error', 'Terjadi kesalahan sistem saat memproses pengiriman lamaran. Silakan coba kembali.');
        }
    }
}
