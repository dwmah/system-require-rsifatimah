<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobVacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancyQualification;
use App\Models\VacancySelectionStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminVacancyController extends Controller
{
    /**
     * Tampilkan daftar lowongan kerja.
     */
    public function index(Request $request): View
    {
        $query = JobVacancy::with(['recruitmentPeriod', 'documentRequirements'])
            ->withCount('jobApplications');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('employment_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', JobVacancyStatus::OPEN);
            } elseif ($request->status === 'inactive') {
                $query->where('status', '!=', JobVacancyStatus::OPEN);
            }
        }

        if ($request->filled('formation_type')) {
            $query->where('employment_type', $request->formation_type);
        }

        $vacancies = $query->latest()->paginate(10)->withQueryString();
        $periods = RecruitmentPeriod::latest()->get();

        return view('admin.vacancies.index', [
            'vacancies' => $vacancies,
            'periods' => $periods,
        ]);
    }

    /**
     * Form pembuatan lowongan baru.
     */
    public function create(): View
    {
        $periods = RecruitmentPeriod::latest()->get();

        return view('admin.vacancies.create', [
            'periods' => $periods,
        ]);
    }

    /**
     * Simpan data lowongan baru beserta kualifikasi, dokumen wajib, & tahapan seleksi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recruitment_period_id' => 'required|exists:recruitment_periods,id',
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'formation_type' => 'required|string|max:100',
            'quota' => 'required|integer|min:1',
            'summary' => 'nullable|string',
            'description' => 'required|string',
            'is_published' => 'boolean',
            'qualifications' => 'nullable|array',
            'qualifications.*' => 'nullable|string',
            'document_names' => 'nullable|array',
            'document_names.*' => 'nullable|string',
            'document_mandatory' => 'nullable|array',
            'stages' => 'nullable|array',
            'stages.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $code = 'VAC-' . strtoupper(Str::slug($request->title, '')) . '-' . rand(100, 999);

            $vacancy = JobVacancy::create([
                'recruitment_period_id' => $validated['recruitment_period_id'],
                'code' => $code,
                'title' => $validated['title'],
                'unit' => $validated['department'] ?? 'Pelayanan Medis',
                'employment_type' => $validated['formation_type'],
                'location' => 'RSI Fatimah Banyuwangi',
                'quota' => $validated['quota'],
                'description' => $validated['description'],
                'open_at' => now(),
                'close_at' => now()->addMonth(),
                'status' => $request->has('is_published') ? JobVacancyStatus::OPEN : JobVacancyStatus::DRAFT,
            ]);

            // Kualifikasi
            if (!empty($request->qualifications)) {
                foreach ($request->qualifications as $idx => $desc) {
                    if (trim($desc ?? '') !== '') {
                        VacancyQualification::create([
                            'job_vacancy_id' => $vacancy->id,
                            'label' => 'Kualifikasi ' . ($idx + 1),
                            'value' => trim($desc),
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }
            }

            // Persyaratan Dokumen
            if (!empty($request->document_names)) {
                foreach ($request->document_names as $idx => $docName) {
                    if (trim($docName ?? '') !== '') {
                        $isMandatory = isset($request->document_mandatory[$idx]) && $request->document_mandatory[$idx] == '1';
                        VacancyDocumentRequirement::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => trim($docName),
                            'is_required' => $isMandatory,
                            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
                            'max_size_kb' => 5120,
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }
            } else {
                // Default persyaratan sesuai standar RSI Fatimah
                $defaultDocs = [
                    ['name' => 'Surat Lamaran Kerja', 'required' => true],
                    ['name' => 'Ijazah Terakhir Legalisir', 'required' => true],
                    ['name' => 'STR / Sertifikat Keahlian Aktif', 'required' => true],
                    ['name' => 'SKCK (Kepolisian)', 'required' => false],
                    ['name' => 'Rekomendasi Profesi / Ormas', 'required' => false],
                    ['name' => 'Dokumen Pendukung Lainnya', 'required' => false],
                ];

                foreach ($defaultDocs as $idx => $doc) {
                    VacancyDocumentRequirement::create([
                        'job_vacancy_id' => $vacancy->id,
                        'name' => $doc['name'],
                        'is_required' => $doc['required'],
                        'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
                        'max_size_kb' => 5120,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }

            // Tahapan Seleksi
            if (!empty($request->stages)) {
                foreach ($request->stages as $idx => $stageName) {
                    if (trim($stageName ?? '') !== '') {
                        VacancySelectionStage::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => trim($stageName),
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }
            } else {
                $defaultStages = [
                    'Seleksi Administrasi & Verifikasi Berkas',
                    'Ujian Tulis & Kompetensi Bidang',
                    'Wawancara SDI & Direksi',
                    'Medical Check-Up (MCU)',
                    'Pengumuman Kelulusan Akhir',
                ];

                foreach ($defaultStages as $idx => $sName) {
                    VacancySelectionStage::create([
                        'job_vacancy_id' => $vacancy->id,
                        'name' => $sName,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.vacancies.index')->with('success', 'Formasi lowongan kerja berhasil dibuat!');
    }

    /**
     * Tampilkan detail lowongan dan pelamar yang melamar.
     */
    public function show(JobVacancy $vacancy): View
    {
        $vacancy->load([
            'recruitmentPeriod',
            'qualifications',
            'documentRequirements',
            'selectionStages',
            'jobApplications.user.applicantProfile',
        ]);

        return view('admin.vacancies.show', [
            'vacancy' => $vacancy,
        ]);
    }

    /**
     * Form edit lowongan.
     */
    public function edit(JobVacancy $vacancy): View
    {
        $vacancy->load(['qualifications', 'documentRequirements', 'selectionStages']);
        $periods = RecruitmentPeriod::latest()->get();

        return view('admin.vacancies.edit', [
            'vacancy' => $vacancy,
            'periods' => $periods,
        ]);
    }

    /**
     * Update data lowongan.
     */
    public function update(Request $request, JobVacancy $vacancy): RedirectResponse
    {
        $validated = $request->validate([
            'recruitment_period_id' => 'required|exists:recruitment_periods,id',
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'formation_type' => 'required|string|max:100',
            'quota' => 'required|integer|min:1',
            'description' => 'required|string',
            'is_published' => 'boolean',
        ]);

        $vacancy->update([
            'recruitment_period_id' => $validated['recruitment_period_id'],
            'title' => $validated['title'],
            'unit' => $validated['department'] ?? 'Pelayanan Medis',
            'employment_type' => $validated['formation_type'],
            'quota' => $validated['quota'],
            'description' => $validated['description'],
            'status' => $request->has('is_published') ? JobVacancyStatus::OPEN : JobVacancyStatus::DRAFT,
        ]);

        return redirect()->route('admin.vacancies.show', $vacancy)->with('success', 'Data lowongan berhasil diperbarui.');
    }

    /**
     * Toggle status publikasi lowongan.
     */
    public function togglePublish(JobVacancy $vacancy): RedirectResponse
    {
        $newStatus = $vacancy->status === JobVacancyStatus::OPEN ? JobVacancyStatus::DRAFT : JobVacancyStatus::OPEN;
        $vacancy->update(['status' => $newStatus]);

        $statusStr = $newStatus === JobVacancyStatus::OPEN ? 'dipublikasikan (Aktif)' : 'ditutup (Draft/Nonaktif)';

        return back()->with('success', "Status lowongan '{$vacancy->title}' berhasil diubah menjadi {$statusStr}.");
    }

    /**
     * Hapus lowongan jika belum ada pelamar yang mengirimkan lamaran.
     */
    public function destroy(JobVacancy $vacancy): RedirectResponse
    {
        if ($vacancy->jobApplications()->count() > 0) {
            return back()->with('error', 'Lowongan tidak dapat dihapus karena sudah memiliki data pelamar terdaftar.');
        }

        $vacancy->qualifications()->delete();
        $vacancy->documentRequirements()->delete();
        $vacancy->selectionStages()->delete();
        $vacancy->delete();

        return redirect()->route('admin.vacancies.index')->with('success', 'Lowongan kerja berhasil dihapus.');
    }
}
