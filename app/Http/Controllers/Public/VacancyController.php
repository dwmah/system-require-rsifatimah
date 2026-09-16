<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\VacancyQualification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacancyController extends Controller
{
    /**
     * Menampilkan daftar lowongan pekerjaan yang sedang dibuka untuk publik.
     */
    public function index(Request $request): View
    {
        $query = JobVacancy::currentlyOpen();

        // 1. Server-side Search: title, unit, code
        if ($request->filled('q')) {
            $searchTerm = '%' . trim($request->input('q')) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('unit', 'like', $searchTerm)
                    ->orWhere('code', 'like', $searchTerm);
            });
        }

        // 2. Filter Unit
        if ($request->filled('unit')) {
            $query->where('unit', $request->input('unit'));
        }

        // 3. Filter Employment Type
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->input('employment_type'));
        }

        // 4. Filter Kualifikasi Pendidikan Formal
        if ($request->filled('education')) {
            $eduTerm = '%' . trim($request->input('education')) . '%';
            $query->whereHas('qualifications', function ($q) use ($eduTerm) {
                $q->where('label', VacancyQualification::LABEL_EDUCATION)
                    ->where('value', 'like', $eduTerm);
            });
        }

        // Data opsi filter dinamis dari lowongan yang sedang terbuka
        $availableUnits = JobVacancy::currentlyOpen()->distinct()->orderBy('unit')->pluck('unit');
        $availableEmploymentTypes = JobVacancy::currentlyOpen()->distinct()->orderBy('employment_type')->pluck('employment_type');

        $educationTiers = [
            'D3' => 'D3 (Diploma 3)',
            'D4' => 'D4 (Diploma 4)',
            'S1' => 'S1 (Sarjana)',
            'Ners' => 'Profesi Ners',
            'Dokter' => 'Profesi Dokter',
            'Apoteker' => 'Profesi Apoteker',
        ];

        $totalOpenVacancies = JobVacancy::currentlyOpen()->count();

        // Pagination 6 item per halaman (grid 3 kolom desktop)
        $vacancies = $query->latest('open_at')->paginate(6)->withQueryString();

        return view('public.vacancies.index', compact(
            'vacancies',
            'availableUnits',
            'availableEmploymentTypes',
            'educationTiers',
            'totalOpenVacancies'
        ));
    }

    /**
     * Menampilkan detail informasi lowongan pekerjaan berdasarkan kode unik lowongan.
     */
    public function show(JobVacancy $jobVacancy): View
    {
        // Pastikan lowongan benar-benar sedang dibuka untuk publik
        if (! $jobVacancy->isCurrentlyOpen()) {
            abort(404, 'Lowongan pekerjaan tidak ditemukan atau pendaftaran telah ditutup.');
        }

        // Eager load seluruh relasi dengan urutan sort_order untuk mencegah N+1 query
        $jobVacancy->load([
            'recruitmentPeriod',
            'qualifications',
            'documentRequirements',
            'selectionStages',
        ]);

        $existingApplication = null;
        if (auth()->check()) {
            $existingApplication = auth()->user()
                ->jobApplications()
                ->where('recruitment_period_id', $jobVacancy->recruitment_period_id)
                ->with(['jobVacancy', 'documents'])
                ->first();
        }

        return view('public.vacancies.show', compact('jobVacancy', 'existingApplication'));
    }
}
