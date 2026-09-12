<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Portal\PortalHomeController;
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
        Route::post('/daftar', [RegisteredUserController::class, 'store'])->name('register.store');
    });
});

// Autentikasi Pelamar (TIDAK dibatasi recruitment.open agar pelamar terdaftar tetap bisa login saat rekrutmen tutup)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    // Forgot Password & Reset Password
    Route::get('/lupa-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Logout & Portal Pelamar (Membutuhkan Auth dan Akun Aktif, TIDAK dibatasi recruitment.open)
Route::middleware(['auth', 'applicant.active'])->group(function () {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/portal', [PortalHomeController::class, 'index'])->name('portal.home');

    // Profil Pelamar
    Route::get('/portal/profil', [\App\Http\Controllers\Portal\ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/portal/profil/data-pribadi', [\App\Http\Controllers\Portal\ProfileController::class, 'updatePersonal'])->name('profile.personal.update');
    Route::patch('/portal/profil/alamat-kontak', [\App\Http\Controllers\Portal\ProfileController::class, 'updateContact'])->name('profile.contact.update');

    // Riwayat Pendidikan
    Route::post('/portal/profil/pendidikan', [\App\Http\Controllers\Portal\ApplicantEducationController::class, 'store'])->name('profile.educations.store');
    Route::patch('/portal/profil/pendidikan/{education}', [\App\Http\Controllers\Portal\ApplicantEducationController::class, 'update'])->name('profile.educations.update');
    Route::delete('/portal/profil/pendidikan/{education}', [\App\Http\Controllers\Portal\ApplicantEducationController::class, 'destroy'])->name('profile.educations.destroy');

    // Lisensi / STR
    Route::post('/portal/profil/lisensi', [\App\Http\Controllers\Portal\ApplicantLicenseController::class, 'store'])->name('profile.licenses.store');
    Route::patch('/portal/profil/lisensi/{license}', [\App\Http\Controllers\Portal\ApplicantLicenseController::class, 'update'])->name('profile.licenses.update');
    Route::delete('/portal/profil/lisensi/{license}', [\App\Http\Controllers\Portal\ApplicantLicenseController::class, 'destroy'])->name('profile.licenses.destroy');

    // Sertifikasi
    Route::post('/portal/profil/sertifikasi', [\App\Http\Controllers\Portal\ApplicantCertificationController::class, 'store'])->name('profile.certifications.store');
    Route::patch('/portal/profil/sertifikasi/{certification}', [\App\Http\Controllers\Portal\ApplicantCertificationController::class, 'update'])->name('profile.certifications.update');
    Route::delete('/portal/profil/sertifikasi/{certification}', [\App\Http\Controllers\Portal\ApplicantCertificationController::class, 'destroy'])->name('profile.certifications.destroy');

    // Pengalaman Kerja
    Route::post('/portal/profil/pengalaman', [\App\Http\Controllers\Portal\ApplicantWorkExperienceController::class, 'store'])->name('profile.work-experiences.store');
    Route::patch('/portal/profil/pengalaman/{workExperience}', [\App\Http\Controllers\Portal\ApplicantWorkExperienceController::class, 'update'])->name('profile.work-experiences.update');
    Route::delete('/portal/profil/pengalaman/{workExperience}', [\App\Http\Controllers\Portal\ApplicantWorkExperienceController::class, 'destroy'])->name('profile.work-experiences.destroy');

    // Alur Lamaran Pekerjaan (Fase 8A)
    Route::get('/portal/lamaran', [\App\Http\Controllers\Portal\ApplicationController::class, 'index'])->name('portal.applications.index');
    Route::get('/portal/lamaran/mulai/{jobVacancy}', [\App\Http\Controllers\Portal\ApplicationController::class, 'start'])->name('portal.applications.start');
    Route::post('/portal/lamaran/mulai/{jobVacancy}', [\App\Http\Controllers\Portal\ApplicationController::class, 'store'])->name('portal.applications.store');
    Route::get('/portal/lamaran/{jobApplication}', [\App\Http\Controllers\Portal\ApplicationController::class, 'show'])
        ->whereNumber('jobApplication')
        ->name('portal.applications.show');
});

// Development Only: Design System & Component Visual Preview
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/design-system', function () {
        return view('dev.design-system');
    })->name('dev.design-system');
}
