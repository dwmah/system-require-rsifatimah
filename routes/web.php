<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Portal\ApplicantCertificationController;
use App\Http\Controllers\Portal\ApplicantEducationController;
use App\Http\Controllers\Portal\ApplicantLicenseController;
use App\Http\Controllers\Portal\ApplicantWorkExperienceController;
use App\Http\Controllers\Portal\ApplicationController;
use App\Http\Controllers\Portal\ApplicationDocumentController;
use App\Http\Controllers\Portal\ApplicationSubmissionController;
use App\Http\Controllers\Portal\ApplicationTrackingController;
use App\Http\Controllers\Portal\PortalHomeController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Public\GuideController;
use App\Http\Controllers\Public\VacancyController;
use Illuminate\Support\Facades\Route;

// Halaman Informasi Rekrutmen Sedang Ditutup (tidak diproteksi middleware penutupan)
Route::get('/rekrutmen-ditutup', function () {
    return view('public.recruitment-closed');
})->name('recruitment.closed');

// Portal Rekrutmen Publik (Diproteksi middleware: hanya dapat diakses saat ada rekrutmen aktif)
Route::middleware(['recruitment.open'])->group(function () {
    Route::get('/', [VacancyController::class, 'index'])->name('recruitment.index');
    Route::get('/lowongan', [VacancyController::class, 'index'])->name('vacancies.index');
    Route::get('/lowongan/{jobVacancy}', [VacancyController::class, 'show'])->name('vacancies.show');
    Route::get('/panduan/alur-pendaftaran', [GuideController::class, 'flow'])->name('guides.flow');
    Route::get('/panduan/persyaratan', [GuideController::class, 'requirements'])->name('guides.requirements');

    // Registrasi Pelamar Baru (Hanya bisa saat recruitment terbuka & belum login)
    Route::middleware('guest')->group(function () {
        Route::get('/daftar', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/daftar', [RegisteredUserController::class, 'store'])
            ->middleware('throttle:registration')
            ->name('register.store');
    });
});

// Autentikasi Pelamar (TIDAK dibatasi recruitment.open agar pelamar terdaftar tetap bisa login saat rekrutmen tutup)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    // Forgot Password & Reset Password
    Route::get('/lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:password-reset-link')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Logout & Portal Pelamar (Membutuhkan Auth dan Akun Aktif, TIDAK dibatasi recruitment.open)
Route::middleware(['auth', 'applicant.active'])->group(function () {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/portal', [PortalHomeController::class, 'index'])->name('portal.home');

    // Profil Pelamar
    Route::get('/portal/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/portal/profil/data-pribadi', [ProfileController::class, 'updatePersonal'])->name('profile.personal.update');
    Route::patch('/portal/profil/alamat-kontak', [ProfileController::class, 'updateContact'])->name('profile.contact.update');

    // Riwayat Pendidikan
    Route::post('/portal/profil/pendidikan', [ApplicantEducationController::class, 'store'])->name('profile.educations.store');
    Route::patch('/portal/profil/pendidikan/{education}', [ApplicantEducationController::class, 'update'])->name('profile.educations.update');
    Route::delete('/portal/profil/pendidikan/{education}', [ApplicantEducationController::class, 'destroy'])->name('profile.educations.destroy');

    // Lisensi / STR
    Route::post('/portal/profil/lisensi', [ApplicantLicenseController::class, 'store'])->name('profile.licenses.store');
    Route::patch('/portal/profil/lisensi/{license}', [ApplicantLicenseController::class, 'update'])->name('profile.licenses.update');
    Route::delete('/portal/profil/lisensi/{license}', [ApplicantLicenseController::class, 'destroy'])->name('profile.licenses.destroy');

    // Sertifikasi
    Route::post('/portal/profil/sertifikasi', [ApplicantCertificationController::class, 'store'])->name('profile.certifications.store');
    Route::patch('/portal/profil/sertifikasi/{certification}', [ApplicantCertificationController::class, 'update'])->name('profile.certifications.update');
    Route::delete('/portal/profil/sertifikasi/{certification}', [ApplicantCertificationController::class, 'destroy'])->name('profile.certifications.destroy');

    // Pengalaman Kerja
    Route::post('/portal/profil/pengalaman', [ApplicantWorkExperienceController::class, 'store'])->name('profile.work-experiences.store');
    Route::patch('/portal/profil/pengalaman/{workExperience}', [ApplicantWorkExperienceController::class, 'update'])->name('profile.work-experiences.update');
    Route::delete('/portal/profil/pengalaman/{workExperience}', [ApplicantWorkExperienceController::class, 'destroy'])->name('profile.work-experiences.destroy');

    // Alur Lamaran Pekerjaan (Fase 8A & 8B)
    Route::get('/portal/lamaran', [ApplicationController::class, 'index'])->name('portal.applications.index');
    Route::get('/portal/lamaran/mulai/{jobVacancy}', [ApplicationController::class, 'start'])->name('portal.applications.start');
    Route::post('/portal/lamaran/mulai/{jobVacancy}', [ApplicationController::class, 'store'])->name('portal.applications.store');
    Route::get('/portal/lamaran/{jobApplication}', [ApplicationController::class, 'show'])
        ->whereNumber('jobApplication')
        ->name('portal.applications.show');

    // Berkas Persyaratan & Final Submit (Fase 8B)
    Route::get('/portal/lamaran/{jobApplication}/berkas', [ApplicationDocumentController::class, 'index'])
        ->whereNumber('jobApplication')
        ->name('portal.applications.documents.index');
    Route::post('/portal/lamaran/{jobApplication}/berkas/{requirement}', [ApplicationDocumentController::class, 'store'])
        ->whereNumber('jobApplication')
        ->whereNumber('requirement')
        ->name('portal.applications.documents.store');
    Route::delete('/portal/lamaran/{jobApplication}/berkas/{document}', [ApplicationDocumentController::class, 'destroy'])
        ->whereNumber('jobApplication')
        ->whereNumber('document')
        ->name('portal.applications.documents.destroy');
    Route::get('/portal/lamaran/{jobApplication}/berkas/{document}/download', [ApplicationDocumentController::class, 'download'])
        ->whereNumber('jobApplication')
        ->whereNumber('document')
        ->name('portal.applications.documents.download');

    Route::get('/portal/lamaran/{jobApplication}/review', [ApplicationSubmissionController::class, 'review'])
        ->whereNumber('jobApplication')
        ->name('portal.applications.review');
    Route::post('/portal/lamaran/{jobApplication}/submit', [ApplicationSubmissionController::class, 'submit'])
        ->whereNumber('jobApplication')
        ->name('portal.applications.submit');

    // Tracking Proses Seleksi Pelamar (Fase 9)
    Route::get('/portal/lamaran/{jobApplication}/tracking', [ApplicationTrackingController::class, 'show'])
        ->whereNumber('jobApplication')
        ->name('portal.applications.tracking');
});

// Development Only: Design System & Component Visual Preview
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/design-system', function () {
        return view('dev.design-system');
    })->name('dev.design-system');
}
