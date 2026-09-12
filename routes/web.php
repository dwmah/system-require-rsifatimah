<?php

use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLifecycleController;
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
use App\Http\Controllers\Portal\PortalHomeController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Public\GuideController;
use App\Http\Controllers\Public\VacancyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Rekrutmen RSI Fatimah Banyuwangi
|--------------------------------------------------------------------------
*/

// Halaman Pengumuman Pendaftaran Ditutup
Route::get('/rekrutmen-ditutup', function () {
    return view('public.recruitment-closed');
})->name('recruitment.closed');

// Portal Rekrutmen Publik (Informasi Terbuka)
Route::middleware('recruitment.open')->group(function () {
    Route::get('/', [VacancyController::class, 'index'])->name('recruitment.index');
    Route::get('/lowongan', [VacancyController::class, 'index'])->name('vacancies.index');
    Route::get('/lowongan/{jobVacancy}', [VacancyController::class, 'show'])->name('vacancies.show');
    Route::get('/panduan/alur-pendaftaran', [GuideController::class, 'flow'])->name('guides.flow');
    Route::get('/panduan/persyaratan', [GuideController::class, 'requirements'])->name('guides.requirements');
});

// Registrasi Pelamar Baru (Hanya bisa saat recruitment terbuka & guest)
Route::middleware(['recruitment.open', 'guest'])->group(function () {
    Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisteredUserController::class, 'store'])->name('register.store');
});

// Autentikasi Pelamar & Admin (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Shared Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dokumen/download/{document}', [ApplicantDocumentController::class, 'download'])->name('documents.download');
});

// =========================================================================
// PORTAL PELAMAR (APPLICANT PORTAL)
// =========================================================================
Route::middleware(['auth', 'applicant'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalHomeController::class, 'index'])->name('home');

    // Lamaran Saya & Workspace Pengunggahan Dokumen
    Route::get('/lamaran', [ApplicantApplicationController::class, 'index'])->name('applications.index');
    Route::get('/lamaran/pilih-formasi', [ApplicantApplicationController::class, 'create'])->name('applications.create');
    Route::post('/lamaran/pilih-formasi', [ApplicantApplicationController::class, 'store'])->name('applications.store');
    Route::post('/lamaran/{application}/submit', [ApplicantApplicationController::class, 'submit'])->name('applications.submit');
    Route::post('/lamaran/{application}/dokumen', [ApplicantDocumentController::class, 'store'])->name('applications.documents.store');
    Route::delete('/lamaran/{application}/dokumen/{document}', [ApplicantDocumentController::class, 'destroy'])->name('applications.documents.destroy');
    Route::post('/lamaran/{application}/konfirmasi-pengumuman', [ApplicantApplicationController::class, 'acknowledgeAnnouncement'])->name('applications.acknowledge');

    // Profil Pelamar
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profil/data-pribadi', [ProfileController::class, 'updatePersonal'])->name('profile.personal.update');
    Route::patch('/profil/alamat-kontak', [ProfileController::class, 'updateContact'])->name('profile.contact.update');

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
    Route::post('/notifikasi/{notification}/read', [ApplicantNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [ApplicantNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Backward compatibility profile alias for existing tests/views
Route::middleware(['auth', 'applicant'])->prefix('portal/profil')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::patch('/data-pribadi', [ProfileController::class, 'updatePersonal'])->name('personal.update');
    Route::patch('/alamat-kontak', [ProfileController::class, 'updateContact'])->name('contact.update');
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
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/verify', [AdminApplicationController::class, 'verify'])->name('applications.verify');
    Route::post('/applications/documents/{document}/verify', [AdminApplicationController::class, 'verifyDocument'])->name('applications.documents.verify');
    Route::post('/applications/{application}/update-stage', [AdminApplicationController::class, 'updateStage'])->name('applications.update-stage');

    // Manajemen Timeline & Kalender Seleksi
    Route::get('/timelines', [AdminTimelineController::class, 'index'])->name('timelines.index');
    Route::post('/timelines', [AdminTimelineController::class, 'store'])->name('timelines.store');
    Route::match(['put', 'patch'], '/timelines/{period}', [AdminTimelineController::class, 'update'])->name('timelines.update');

    // Manajemen Siklus Akun (User Lifecycle)
    Route::get('/lifecycle', [AdminLifecycleController::class, 'index'])->name('lifecycle.index');
    Route::post('/lifecycle/{user}/deactivate', [AdminLifecycleController::class, 'deactivate'])->name('lifecycle.deactivate');
    Route::post('/lifecycle/{user}/reactivate', [AdminLifecycleController::class, 'reactivate'])->name('lifecycle.reactivate');
    Route::delete('/lifecycle/{user}/cleanup', [AdminLifecycleController::class, 'cleanup'])->name('lifecycle.cleanup');
    Route::post('/lifecycle/cleanup-all-seen', [AdminLifecycleController::class, 'cleanupAllSeen'])->name('lifecycle.cleanup-all-seen');
});

// Development Only: Preview Design System
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/design-system', function () {
        return view('dev.design-system');
    })->name('dev.design-system');
}
