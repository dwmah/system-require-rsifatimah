<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobVacancyStatus;
use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancyQualification;
use App\Models\VacancySelectionStage;
use Carbon\Carbon;
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
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'summary' => 'nullable|string',
            'description' => 'required|string',
            'is_published' => 'boolean',
            'qualifications' => 'nullable|array',
            'qualifications.*' => 'nullable|string',
            'document_names' => 'nullable|array',
            'document_names.*' => 'nullable|string',
            'document_descriptions' => 'nullable|array',
            'document_mandatory' => 'nullable|array',
            'document_mimes' => 'nullable|array',
            'document_max_sizes' => 'nullable|array',
            'stages' => 'nullable|array',
            'stages.*' => 'nullable|string',
            'stage_names' => 'nullable|array',
            'stage_names.*' => 'nullable|string',
            'stage_descriptions' => 'nullable|array',
            'stage_start_ats' => 'nullable|array',
            'stage_end_ats' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $code = 'VAC-'.strtoupper(Str::slug($request->title, '')).'-'.rand(100, 999);

            $openAt = $request->filled('open_at')
                ? (strlen($request->open_at) === 10 ? Carbon::parse($request->open_at)->startOfDay() : Carbon::parse($request->open_at))
                : now();

            $closeAt = $request->filled('close_at')
                ? (strlen($request->close_at) === 10 ? Carbon::parse($request->close_at)->endOfDay() : Carbon::parse($request->close_at))
                : now()->addMonth()->endOfDay();

            $vacancy = JobVacancy::create([
                'recruitment_period_id' => $validated['recruitment_period_id'],
                'code' => $code,
                'title' => $validated['title'],
                'unit' => $validated['department'] ?? 'Pelayanan Medis',
                'employment_type' => $validated['formation_type'],
                'location' => 'RSI Fatimah Banyuwangi',
                'quota' => $validated['quota'],
                'description' => $validated['description'],
                'open_at' => $openAt,
                'close_at' => $closeAt,
                'status' => $request->has('is_published') ? JobVacancyStatus::OPEN : JobVacancyStatus::DRAFT,
            ]);

            // Kualifikasi
            if (! empty($request->qualifications)) {
                $qualLabels = (array) $request->input('qualification_labels', []);
                foreach ($request->qualifications as $idx => $desc) {
                    if (trim($desc ?? '') !== '') {
                        $label = VacancyQualification::resolveSemanticLabel(
                            trim($desc),
                            $qualLabels[$idx] ?? null,
                            $idx
                        );

                        VacancyQualification::create([
                            'job_vacancy_id' => $vacancy->id,
                            'label' => $label,
                            'value' => trim($desc),
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }
            }

            // Persyaratan Dokumen
            $docNames = $request->input('document_names', []);
            $docMandatories = (array) $request->input('document_mandatory', []);
            $docDescriptions = (array) $request->input('document_descriptions', []);
            $docMimes = (array) $request->input('document_mimes', []);
            $docMaxSizes = (array) $request->input('document_max_sizes', []);

            $hasValidCustomDocs = false;
            if (! empty($docNames)) {
                foreach ($docNames as $idx => $docName) {
                    if (trim($docName ?? '') !== '') {
                        $hasValidCustomDocs = true;
                        $isMandatory = isset($docMandatories[$idx]) && ($docMandatories[$idx] === '1' || $docMandatories[$idx] === 1 || $docMandatories[$idx] === true || $docMandatories[$idx] === 'true');
                        $desc = $docDescriptions[$idx] ?? null;
                        $mimes = ! empty($docMimes[$idx]) ? $docMimes[$idx] : 'application/pdf';
                        $maxSize = ! empty($docMaxSizes[$idx]) ? (int) $docMaxSizes[$idx] : 5120;

                        VacancyDocumentRequirement::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => trim($docName),
                            'description' => $desc,
                            'is_required' => $isMandatory,
                            'allowed_mimes' => $mimes,
                            'max_size_kb' => $maxSize,
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }
            }

            if (! $hasValidCustomDocs && empty($docNames)) {
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
                        'allowed_mimes' => 'application/pdf',
                        'max_size_kb' => 5120,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }

            // Tahapan Seleksi
            $stageNames = (array) ($request->input('stage_names') ?? $request->input('stages') ?? []);
            $stageDescriptions = (array) $request->input('stage_descriptions', []);
            $stageStartAts = (array) $request->input('stage_start_ats', []);
            $stageEndAts = (array) $request->input('stage_end_ats', []);

            $hasValidStages = false;
            if (! empty($stageNames)) {
                foreach ($stageNames as $idx => $stageName) {
                    if (trim($stageName ?? '') !== '') {
                        $hasValidStages = true;
                        $desc = $stageDescriptions[$idx] ?? null;
                        $startAt = ! empty($stageStartAts[$idx]) ? Carbon::parse($stageStartAts[$idx]) : null;
                        $endAt = ! empty($stageEndAts[$idx]) ? Carbon::parse($stageEndAts[$idx]) : null;

                        VacancySelectionStage::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => trim($stageName),
                            'description' => $desc,
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'sort_order' => $idx + 1,
                        ]);
                    }
                }
            }

            if (! $hasValidStages && empty($stageNames)) {
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
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'description' => 'required|string',
            'is_published' => 'boolean',
            'qualifications' => 'nullable|array',
            'qualifications.*' => 'nullable|string',
            'document_names' => 'nullable|array',
            'document_names.*' => 'nullable|string',
            'document_descriptions' => 'nullable|array',
            'document_mandatory' => 'nullable|array',
            'document_mimes' => 'nullable|array',
            'document_max_sizes' => 'nullable|array',
            'document_ids' => 'nullable|array',
            'stages' => 'nullable|array',
            'stages.*' => 'nullable|string',
            'stage_names' => 'nullable|array',
            'stage_names.*' => 'nullable|string',
            'stage_descriptions' => 'nullable|array',
            'stage_start_ats' => 'nullable|array',
            'stage_end_ats' => 'nullable|array',
            'stage_ids' => 'nullable|array',
        ]);

        $updateData = [
            'recruitment_period_id' => $validated['recruitment_period_id'],
            'title' => $validated['title'],
            'unit' => $validated['department'] ?? 'Pelayanan Medis',
            'employment_type' => $validated['formation_type'],
            'quota' => $validated['quota'],
            'description' => $validated['description'],
            'status' => $request->has('is_published') ? JobVacancyStatus::OPEN : JobVacancyStatus::DRAFT,
        ];

        if ($request->filled('open_at')) {
            $updateData['open_at'] = strlen($request->open_at) === 10
                ? Carbon::parse($request->open_at)->startOfDay()
                : Carbon::parse($request->open_at);
        }

        if ($request->filled('close_at')) {
            $updateData['close_at'] = strlen($request->close_at) === 10
                ? Carbon::parse($request->close_at)->endOfDay()
                : Carbon::parse($request->close_at);
        }

        DB::transaction(function () use ($request, $vacancy, $updateData) {
            $vacancy->update($updateData);

            // 1. Update Kualifikasi
            if ($request->has('qualifications')) {
                $vacancy->qualifications()->delete();
                if (! empty($request->qualifications)) {
                    $qualLabels = (array) $request->input('qualification_labels', []);
                    foreach ($request->qualifications as $idx => $desc) {
                        if (trim($desc ?? '') !== '') {
                            $label = VacancyQualification::resolveSemanticLabel(
                                trim($desc),
                                $qualLabels[$idx] ?? null,
                                $idx
                            );

                            VacancyQualification::create([
                                'job_vacancy_id' => $vacancy->id,
                                'label' => $label,
                                'value' => trim($desc),
                                'sort_order' => $idx + 1,
                            ]);
                        }
                    }
                }
            }

            // 2. Update Persyaratan Berkas Dokumen
            if ($request->has('document_names')) {
                $existingDocReqs = $vacancy->documentRequirements()->get();
                $usedRequirementIds = DB::table('application_documents')
                    ->whereIn('vacancy_document_requirement_id', $existingDocReqs->pluck('id'))
                    ->pluck('vacancy_document_requirement_id')
                    ->unique()
                    ->toArray();

                $processedIds = [];
                $docNames = (array) $request->input('document_names', []);
                $docMandatories = (array) $request->input('document_mandatory', []);
                $docDescriptions = (array) $request->input('document_descriptions', []);
                $docMimes = (array) $request->input('document_mimes', []);
                $docMaxSizes = (array) $request->input('document_max_sizes', []);
                $docIds = (array) $request->input('document_ids', []);

                foreach ($docNames as $idx => $docName) {
                    if (trim($docName ?? '') === '') {
                        continue;
                    }

                    $docId = ! empty($docIds[$idx]) ? (int) $docIds[$idx] : null;
                    $isMandatory = isset($docMandatories[$idx]) && ($docMandatories[$idx] === '1' || $docMandatories[$idx] === 1 || $docMandatories[$idx] === true || $docMandatories[$idx] === 'true');
                    $desc = $docDescriptions[$idx] ?? null;
                    $mimes = ! empty($docMimes[$idx]) ? $docMimes[$idx] : 'application/pdf';
                    $maxSize = ! empty($docMaxSizes[$idx]) ? (int) $docMaxSizes[$idx] : 5120;

                    // Jika docId tidak ada, cari requirement lama yang belum terproses dengan nama yang sama
                    if (! $docId && $existingDoc = $existingDocReqs->whereNotIn('id', $processedIds)->firstWhere('name', trim($docName))) {
                        $docId = $existingDoc->id;
                    }

                    if ($docId && $existingDoc = $existingDocReqs->firstWhere('id', $docId)) {
                        $existingDoc->update([
                            'name' => trim($docName),
                            'description' => $desc,
                            'is_required' => $isMandatory,
                            'allowed_mimes' => $mimes,
                            'max_size_kb' => $maxSize,
                            'sort_order' => $idx + 1,
                        ]);
                        $processedIds[] = $existingDoc->id;
                    } else {
                        $newDoc = VacancyDocumentRequirement::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => trim($docName),
                            'description' => $desc,
                            'is_required' => $isMandatory,
                            'allowed_mimes' => $mimes,
                            'max_size_kb' => $maxSize,
                            'sort_order' => $idx + 1,
                        ]);
                        $processedIds[] = $newDoc->id;
                    }
                }

                foreach ($existingDocReqs as $existing) {
                    if (! in_array($existing->id, $processedIds, true)) {
                        if (! in_array($existing->id, $usedRequirementIds, true)) {
                            $existing->delete();
                        }
                    }
                }
            }

            // 3. Update Tahapan Seleksi
            if ($request->has('stage_names') || $request->has('stages')) {
                $stageNames = (array) ($request->input('stage_names') ?? $request->input('stages') ?? []);
                $stageDescriptions = (array) $request->input('stage_descriptions', []);
                $stageStartAts = (array) $request->input('stage_start_ats', []);
                $stageEndAts = (array) $request->input('stage_end_ats', []);
                $stageIds = (array) $request->input('stage_ids', []);

                $existingStages = $vacancy->selectionStages()->get();
                $usedStageIds = DB::table('application_stage_histories')
                    ->whereIn('vacancy_selection_stage_id', $existingStages->pluck('id'))
                    ->pluck('vacancy_selection_stage_id')
                    ->unique()
                    ->toArray();

                $processedStageIds = [];

                foreach ($stageNames as $idx => $stageName) {
                    if (trim($stageName ?? '') === '') {
                        continue;
                    }

                    $stageId = ! empty($stageIds[$idx]) ? (int) $stageIds[$idx] : null;
                    $desc = $stageDescriptions[$idx] ?? null;
                    $startAt = ! empty($stageStartAts[$idx]) ? Carbon::parse($stageStartAts[$idx]) : null;
                    $endAt = ! empty($stageEndAts[$idx]) ? Carbon::parse($stageEndAts[$idx]) : null;

                    // Jika stageId tidak ada, cari tahapan lama yang belum terproses dengan nama yang sama
                    if (! $stageId && $existingStage = $existingStages->whereNotIn('id', $processedStageIds)->firstWhere('name', trim($stageName))) {
                        $stageId = $existingStage->id;
                    }

                    if ($stageId && $existingStage = $existingStages->firstWhere('id', $stageId)) {
                        $existingStage->update([
                            'name' => trim($stageName),
                            'description' => $desc,
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'sort_order' => $idx + 1,
                        ]);
                        $processedStageIds[] = $existingStage->id;
                    } else {
                        $newStage = VacancySelectionStage::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => trim($stageName),
                            'description' => $desc,
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'sort_order' => $idx + 1,
                        ]);
                        $processedStageIds[] = $newStage->id;
                    }
                }

                foreach ($existingStages as $existing) {
                    if (! in_array($existing->id, $processedStageIds, true)) {
                        if (! in_array($existing->id, $usedStageIds, true)) {
                            $existing->delete();
                        }
                    }
                }
            }
        });

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
