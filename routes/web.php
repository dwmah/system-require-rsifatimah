<?php

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
});

// Development Only: Design System & Component Visual Preview
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/design-system', function () {
        return view('dev.design-system');
    })->name('dev.design-system');
}
