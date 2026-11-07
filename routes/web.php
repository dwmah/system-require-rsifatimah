<?php

use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminGuideController;
use App\Http\Controllers\Admin\AdminLifecycleController;
use App\Http\Controllers\Admin\AdminRecapController;
use App\Http\Controllers\Admin\AdminTimelineController;
use App\Http\Controllers\Admin\AdminVacancyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Portal\ApplicantApplicationController;
use App\Http\Controllers\Portal\ApplicantCertificationController;
use App\Http\Controllers\Portal\ApplicantDocumentController;
use App\Http\Controllers\Portal\ApplicantEducationController;
use App\Http\Controllers\Portal\ApplicantLicenseController;
use App\Http\Controllers\Portal\ApplicantNotificationController;
use App\Http\Controllers\Portal\ApplicantWorkExperienceController;
use App\Http\Controllers\Portal\ApplicationController;
use App\Http\Controllers\Portal\ApplicationDocumentController;
use App\Http\Controllers\Portal\ApplicationSubmissionController;
use App\Http\Controllers\Portal\ApplicationTrackingController;
use App\Http\Controllers\Portal\PortalHomeController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Public\GuideController;
use App\Http\Controllers\Public\VacancyController;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Rekrutmen RSI Fatimah Banyuwangi
|--------------------------------------------------------------------------
*/

// Halaman Pengumuman Pendaftaran Ditutup
Route::get('/rekrutmen-ditutup', function () {
    return view('public.recruitment-closed');
})->middleware('applicant.redirect')->name('recruitment.closed');

// Portal Rekrutmen Publik (Informasi Terbuka)
Route::middleware(['recruitment.open', 'applicant.redirect'])->group(function () {
    Route::get('/', [VacancyController::class, 'index'])->name('recruitment.index');
    Route::get('/lowongan', [VacancyController::class, 'index'])->name('vacancies.index');
    Route::get('/lowongan/{jobVacancy}', [VacancyController::class, 'show'])->name('vacancies.show');
});

Route::middleware('recruitment.open')->group(function () {
    Route::get('/panduan/alur-pendaftaran', [GuideController::class, 'flow'])->name('guides.flow');
    Route::get('/panduan/persyaratan', [GuideController::class, 'requirements'])->name('guides.requirements');
});

// Registrasi Pelamar Baru (Hanya bisa saat recruitment terbuka & guest)
Route::middleware(['recruitment.open', 'guest'])->group(function () {
    Route::get('/daftar/{jobVacancy?}', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:registration')
        ->name('register.store');
});

// Autentikasi Pelamar & Admin (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:password-reset-link')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Shared Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('applicant.active')->group(function () {
        Route::get('/dokumen/preview/{document}', [ApplicantDocumentController::class, 'preview'])->name('documents.preview');
        Route::get('/dokumen/download/{document}', [ApplicantDocumentController::class, 'download'])->name('documents.download');
    });
});

// =========================================================================
// PORTAL PELAMAR (APPLICANT PORTAL)
// =========================================================================
Route::middleware(['auth', 'applicant'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalHomeController::class, 'index'])->name('home');

    // Alur Lamaran Pekerjaan (Fase 8A & 8B)
    Route::get('/lamaran', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/lamaran/mulai/{jobVacancy}', [ApplicationController::class, 'start'])->name('applications.start');
    Route::post('/lamaran/mulai/{jobVacancy?}', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/lamaran/{jobApplication}', [ApplicationController::class, 'show'])
        ->whereNumber('jobApplication')
        ->name('applications.show');
    Route::match(['delete', 'post'], '/lamaran/{jobApplication}/batal', [ApplicationController::class, 'cancelDraft'])
        ->whereNumber('jobApplication')
        ->name('applications.cancel-draft');

    // Berkas Persyaratan & Final Submit (Fase 8B)
    Route::get('/lamaran/{jobApplication}/berkas', [ApplicationDocumentController::class, 'index'])
        ->whereNumber('jobApplication')
        ->name('applications.documents.index');
    Route::post('/lamaran/{jobApplication}/berkas/{requirement?}', [ApplicationDocumentController::class, 'store'])
        ->whereNumber('jobApplication')
        ->name('applications.documents.store');
    Route::delete('/lamaran/{jobApplication}/berkas/{document}', [ApplicationDocumentController::class, 'destroy'])
        ->whereNumber('jobApplication')
        ->whereNumber('document')
        ->name('applications.documents.destroy');
    Route::get('/lamaran/{jobApplication}/berkas/{document}/download', [ApplicationDocumentController::class, 'download'])
        ->whereNumber('jobApplication')
        ->whereNumber('document')
        ->name('applications.documents.download');

    Route::get('/lamaran/{jobApplication}/review', [ApplicationSubmissionController::class, 'review'])
        ->whereNumber('jobApplication')
        ->name('applications.review');
    Route::post('/lamaran/{jobApplication}/submit', [ApplicationSubmissionController::class, 'submit'])
        ->whereNumber('jobApplication')
        ->name('applications.submit');

    // Tracking Proses Seleksi Pelamar (Fase 9)
    Route::get('/lamaran/{jobApplication}/tracking', [ApplicationTrackingController::class, 'show'])
        ->whereNumber('jobApplication')
        ->name('applications.tracking');
    Route::get('/lamaran/{jobApplication}/stages/{history}/attachment', [ApplicationTrackingController::class, 'downloadStageAttachment'])
        ->whereNumber('jobApplication')
        ->whereNumber('history')
        ->name('applications.stages.download-attachment');

    // Acknowledge Announcement
    Route::post('/lamaran/{application}/konfirmasi-pengumuman', [ApplicantApplicationController::class, 'acknowledgeAnnouncement'])->name('applications.acknowledge');

    // Profil Pelamar
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profil/foto', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profil/foto', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::patch('/profil/data-pribadi', [ProfileController::class, 'updatePersonal'])->name('profile.personal.update');
    Route::patch('/profil/alamat-kontak', [ProfileController::class, 'updateContact'])->name('profile.contact.update');
    Route::patch('/profil', [ProfileController::class, 'updateProfile'])->name('profile.update');

    // Riwayat Pendidikan, Lisensi, Sertifikasi, & Pengalaman
    Route::post('/profil/pendidikan', [ApplicantEducationController::class, 'store'])->name('profile.educations.store');
    Route::patch('/profil/pendidikan/{education}', [ApplicantEducationController::class, 'update'])->name('profile.educations.update');
    Route::delete('/profil/pendidikan/{education}', [ApplicantEducationController::class, 'destroy'])->name('profile.educations.destroy');

    Route::post('/profil/lisensi', [ApplicantLicenseController::class, 'store'])->name('profile.licenses.store');
    Route::patch('/profil/lisensi/{license}', [ApplicantLicenseController::class, 'update'])->name('profile.licenses.update');
    Route::delete('/profil/lisensi/{license}', [ApplicantLicenseController::class, 'destroy'])->name('profile.licenses.destroy');

    Route::post('/profil/sertifikasi', [ApplicantCertificationController::class, 'store'])->name('profile.certifications.store');
    Route::patch('/profil/sertifikasi/{certification}', [ApplicantCertificationController::class, 'update'])->name('profile.certifications.update');
    Route::delete('/profil/sertifikasi/{certification}', [ApplicantCertificationController::class, 'destroy'])->name('profile.certifications.destroy');

    Route::post('/profil/pengalaman', [ApplicantWorkExperienceController::class, 'store'])->name('profile.work-experiences.store');
    Route::patch('/profil/pengalaman/{workExperience}', [ApplicantWorkExperienceController::class, 'update'])->name('profile.work-experiences.update');
    Route::delete('/profil/pengalaman/{workExperience}', [ApplicantWorkExperienceController::class, 'destroy'])->name('profile.work-experiences.destroy');

    // Notifikasi Portal Pelamar
    Route::get('/notifikasi', [ApplicantNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{notification}', [ApplicantNotificationController::class, 'show'])
        ->whereNumber('notification')
        ->name('notifications.show');
    Route::post('/notifikasi/{notification}/read', [ApplicantNotificationController::class, 'markAsRead'])
        ->whereNumber('notification')
        ->name('notifications.read');
    Route::post('/notifikasi/read-all', [ApplicantNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Backward compatibility profile alias for existing tests/views
Route::middleware(['auth', 'applicant'])->prefix('portal/profil')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::post('/foto', [ProfileController::class, 'updatePhoto'])->name('photo.update');
    Route::delete('/foto', [ProfileController::class, 'destroyPhoto'])->name('photo.destroy');
    Route::patch('/data-pribadi', [ProfileController::class, 'updatePersonal'])->name('personal.update');
    Route::patch('/alamat-kontak', [ProfileController::class, 'updateContact'])->name('contact.update');
    Route::patch('/', [ProfileController::class, 'updateProfile'])->name('update');
    Route::post('/pendidikan', [ApplicantEducationController::class, 'store'])->name('educations.store');
    Route::patch('/pendidikan/{education}', [ApplicantEducationController::class, 'update'])->name('educations.update');
    Route::delete('/pendidikan/{education}', [ApplicantEducationController::class, 'destroy'])->name('educations.destroy');
    Route::post('/lisensi', [ApplicantLicenseController::class, 'store'])->name('licenses.store');
    Route::patch('/lisensi/{license}', [ApplicantLicenseController::class, 'update'])->name('licenses.update');
    Route::delete('/lisensi/{license}', [ApplicantLicenseController::class, 'destroy'])->name('licenses.destroy');
    Route::post('/sertifikasi', [ApplicantCertificationController::class, 'store'])->name('certifications.store');
    Route::patch('/sertifikasi/{certification}', [ApplicantCertificationController::class, 'update'])->name('certifications.update');
    Route::delete('/sertifikasi/{certification}', [ApplicantCertificationController::class, 'destroy'])->name('certifications.destroy');
    Route::post('/pengalaman', [ApplicantWorkExperienceController::class, 'store'])->name('work-experiences.store');
    Route::patch('/pengalaman/{workExperience}', [ApplicantWorkExperienceController::class, 'update'])->name('work-experiences.update');
    Route::delete('/pengalaman/{workExperience}', [ApplicantWorkExperienceController::class, 'destroy'])->name('work-experiences.destroy');
});

// =========================================================================
// PANEL ADMIN HRD / SDI (SUPER USER)
// =========================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Utama
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Lowongan Pekerjaan (CRUD)
    Route::get('/jobs', [AdminVacancyController::class, 'index'])->name('vacancies.index');
    Route::get('/jobs/create', [AdminVacancyController::class, 'create'])->name('vacancies.create');
    Route::post('/jobs', [AdminVacancyController::class, 'store'])->name('vacancies.store');
    Route::get('/jobs/{vacancy}', [AdminVacancyController::class, 'show'])->name('vacancies.show');
    Route::get('/jobs/{vacancy}/edit', [AdminVacancyController::class, 'edit'])->name('vacancies.edit');
    Route::match(['put', 'patch'], '/jobs/{vacancy}', [AdminVacancyController::class, 'update'])->name('vacancies.update');
    Route::post('/jobs/{vacancy}/toggle-publish', [AdminVacancyController::class, 'togglePublish'])->name('vacancies.toggle-publish');
    Route::delete('/jobs/{vacancy}', [AdminVacancyController::class, 'destroy'])->name('vacancies.destroy');

    // Manajemen Seluruh Lamaran & Verifikasi Berkas
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/export-biodata', [AdminApplicationController::class, 'exportBiodata'])->name('applications.export-biodata');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::get('/applications/{application}/documents/download-all', [AdminApplicationController::class, 'downloadAllDocuments'])->name('applications.documents.download-all');
    Route::post('/applications/{application}/documents/verify-all', [AdminApplicationController::class, 'verifyAllDocuments'])->name('applications.documents.verify-all');
    Route::post('/applications/{application}/verify', [AdminApplicationController::class, 'verify'])->name('applications.verify');
    Route::get('/applications/{application}/verify', fn (JobApplication $application) => redirect()->route('admin.applications.show', $application));
    Route::post('/applications/documents/{document}/verify', [AdminApplicationController::class, 'verifyDocument'])->name('applications.documents.verify');
    Route::post('/applications/{application}/update-stage', [AdminApplicationController::class, 'updateStage'])->name('applications.update-stage');
    Route::get('/applications/{application}/update-stage', fn (JobApplication $application) => redirect()->route('admin.applications.show', $application));
    Route::get('/applications/{application}/stages/{history}/attachment', [AdminApplicationController::class, 'downloadStageAttachment'])
        ->whereNumber('application')
        ->whereNumber('history')
        ->name('applications.stages.download-attachment');

    // Manajemen Timeline & Kalender Seleksi
    Route::get('/timelines', [AdminTimelineController::class, 'index'])->name('timelines.index');
    Route::post('/timelines', [AdminTimelineController::class, 'store'])->name('timelines.store');
    Route::match(['put', 'patch'], '/timelines/{period}', [AdminTimelineController::class, 'update'])->name('timelines.update');
    Route::post('/timelines/{period}/toggle-status', [AdminTimelineController::class, 'toggleStatus'])->name('timelines.toggle-status');
    Route::post('/timelines/templates', [AdminTimelineController::class, 'storeTemplate'])->name('timelines.templates.store');
    Route::match(['put', 'patch'], '/timelines/templates/{template}', [AdminTimelineController::class, 'updateTemplate'])->name('timelines.templates.update');
    Route::delete('/timelines/templates/{template}', [AdminTimelineController::class, 'destroyTemplate'])->name('timelines.templates.destroy');
    Route::post('/timelines/templates/{template}/apply-to-vacancies', [AdminTimelineController::class, 'applyTemplateToVacancies'])->name('timelines.templates.apply-to-vacancies');

    // Manajemen Konten Panduan (CMS Panduan)
    Route::get('/guides', [AdminGuideController::class, 'index'])->name('guides.index');
    Route::post('/guides', [AdminGuideController::class, 'store'])->name('guides.store');
    Route::get('/guides/{guide}/edit', [AdminGuideController::class, 'edit'])->name('guides.edit');
    Route::match(['put', 'patch'], '/guides/{guide}', [AdminGuideController::class, 'update'])->name('guides.update');
    Route::post('/guides/{guide}/toggle-active', [AdminGuideController::class, 'toggleActive'])->name('guides.toggle-active');

    // Manajemen Siklus Akun (User Lifecycle)
    Route::get('/lifecycle', [AdminLifecycleController::class, 'index'])->name('lifecycle.index');
    Route::get('/lifecycle/export-accepted-biodata', [AdminLifecycleController::class, 'exportAcceptedBiodata'])->name('lifecycle.export-accepted-biodata');
    Route::post('/lifecycle/{user}/deactivate', [AdminLifecycleController::class, 'deactivate'])->name('lifecycle.deactivate');
    Route::post('/lifecycle/{user}/reactivate', [AdminLifecycleController::class, 'reactivate'])->name('lifecycle.reactivate');
    Route::delete('/lifecycle/{user}/cleanup', [AdminLifecycleController::class, 'cleanup'])->name('lifecycle.cleanup')->withTrashed();
    Route::delete('/lifecycle/{user}/force-delete', [AdminLifecycleController::class, 'forceDelete'])->name('lifecycle.force-delete');
    Route::post('/lifecycle/cleanup-all-seen', [AdminLifecycleController::class, 'cleanupAllSeen'])->name('lifecycle.cleanup-all-seen');

    // Rekap Data per Periode Rekrutmen
    Route::get('/rekap-periode', [AdminRecapController::class, 'index'])->name('recap.index');
    Route::get('/rekap-periode/{period}', [AdminRecapController::class, 'show'])->name('recap.show');
    Route::get('/rekap-periode/{period}/export', [AdminRecapController::class, 'export'])->name('recap.export');
});

// Development Only: Preview Design System
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/design-system', function () {
        return view('dev.design-system');
    })->name('dev.design-system');
}
