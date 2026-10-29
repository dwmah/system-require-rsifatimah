<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecruitmentPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\SelectionStageTemplate;
use App\Models\VacancySelectionStage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminTimelineController extends Controller
{
    /**
     * Tampilkan manajemen periode & timeline seleksi serta template tahapan.
     */
    public function index(): View
    {
        $periods = RecruitmentPeriod::with(['jobVacancies.selectionStages'])
            ->latest('registration_start_at')
            ->get();

        $templates = SelectionStageTemplate::orderByDesc('is_default')
            ->latest()
            ->get();

        $activePeriod = RecruitmentPeriod::where('status', RecruitmentPeriodStatus::OPEN)->first();

        return view('admin.timelines.index', [
            'periods' => $periods,
            'templates' => $templates,
            'activePeriod' => $activePeriod,
        ]);
    }

    /**
     * Buat periode rekrutmen baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'submission_deadline' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $isActive = $request->boolean('is_active');

        if ($isActive) {
            RecruitmentPeriod::where('status', RecruitmentPeriodStatus::OPEN)->update(['status' => RecruitmentPeriodStatus::COMPLETED]);
        }

        RecruitmentPeriod::create([
            'name' => $validated['name'],
            'code' => 'PER-'.date('Ymd').'-'.strtoupper(Str::random(4)),
            'registration_start_at' => $validated['start_date'],
            'registration_end_at' => $validated['submission_deadline'],
            'status' => $isActive ? RecruitmentPeriodStatus::OPEN : RecruitmentPeriodStatus::DRAFT,
        ]);

        return back()->with('success', 'Periode rekrutmen baru berhasil ditambahkan.');
    }

    /**
     * Update jadwal tanggal periode seleksi (Date-Driven Lock Configuration).
     */
    public function update(Request $request, RecruitmentPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'submission_deadline' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $isActive = $request->boolean('is_active');

        if ($isActive && $period->status !== RecruitmentPeriodStatus::OPEN) {
            RecruitmentPeriod::where('id', '!=', $period->id)
                ->where('status', RecruitmentPeriodStatus::OPEN)
                ->update(['status' => RecruitmentPeriodStatus::COMPLETED]);
        }

        $period->update([
            'name' => $validated['name'],
            'registration_start_at' => $validated['start_date'],
            'registration_end_at' => $validated['submission_deadline'],
            'status' => $isActive ? RecruitmentPeriodStatus::OPEN : RecruitmentPeriodStatus::COMPLETED,
        ]);

        return back()->with('success', "Jadwal timeline periode '{$period->name}' berhasil diperbarui.");
    }

    /**
     * Switch toggle status buka / tutup periode rekrutmen.
     */
    public function toggleStatus(Request $request, RecruitmentPeriod $period): RedirectResponse
    {
        $targetStatus = $request->input('status');

        if ($targetStatus !== null) {
            $makeOpen = in_array(strtolower($targetStatus), ['open', '1', 'true'], true);
        } else {
            $makeOpen = $period->status !== RecruitmentPeriodStatus::OPEN;
        }

        if ($makeOpen) {
            // Nonaktifkan periode aktif lain
            RecruitmentPeriod::where('id', '!=', $period->id)
                ->where('status', RecruitmentPeriodStatus::OPEN)
                ->update(['status' => RecruitmentPeriodStatus::COMPLETED]);

            $period->update([
                'status' => RecruitmentPeriodStatus::OPEN,
            ]);

            return back()->with('success', "Periode '{$period->name}' berhasil DIBUKA (Aktif).");
        }

        $period->update([
            'status' => RecruitmentPeriodStatus::COMPLETED,
        ]);

        return back()->with('success', "Periode '{$period->name}' berhasil DITUTUP (Nonaktif).");
    }

    /**
     * Simpan template tahapan seleksi baru.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
            'stages' => 'required|array|min:1',
            'stages.*.sequence_order' => 'required|integer',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.description' => 'nullable|string',
            'stages.*.start_date' => 'nullable|date',
            'stages.*.end_date' => 'nullable|date',
            'stages.*.announcement_date' => 'nullable|date',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            SelectionStageTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        SelectionStageTemplate::create([
            'name' => $validated['name'],
            'is_default' => $isDefault,
            'stages_data' => array_values($validated['stages']),
        ]);

        return back()->with('success', 'Template timeline seleksi berhasil disimpan.');
    }

    /**
     * Update template tahapan seleksi.
     */
    public function updateTemplate(Request $request, SelectionStageTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
            'stages' => 'required|array|min:1',
            'stages.*.sequence_order' => 'required|integer',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.description' => 'nullable|string',
            'stages.*.start_date' => 'nullable|date',
            'stages.*.end_date' => 'nullable|date',
            'stages.*.announcement_date' => 'nullable|date',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && ! $template->is_default) {
            SelectionStageTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
        }

        $template->update([
            'name' => $validated['name'],
            'is_default' => $isDefault,
            'stages_data' => array_values($validated['stages']),
        ]);

        return back()->with('success', "Template '{$template->name}' berhasil diperbarui.");
    }

    /**
     * Hapus template tahapan seleksi.
     */
    public function destroyTemplate(SelectionStageTemplate $template): RedirectResponse
    {
        $template->delete();

        return back()->with('success', 'Template timeline seleksi berhasil dihapus.');
    }

    /**
     * Terapkan template tahapan seleksi ke lowongan-lowongan (mass sync).
     */
    public function applyTemplateToVacancies(Request $request, SelectionStageTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => 'nullable|exists:recruitment_periods,id',
            'vacancy_ids' => 'nullable|array',
            'vacancy_ids.*' => 'exists:job_vacancies,id',
        ]);

        $stagesData = $template->stages_data;
        if (empty($stagesData) || ! is_array($stagesData)) {
            return back()->with('error', 'Template tidak memiliki daftar tahapan yang valid.');
        }

        DB::transaction(function () use ($validated, $stagesData) {
            $vacanciesQuery = JobVacancy::query();

            if (! empty($validated['vacancy_ids'])) {
                $vacanciesQuery->whereIn('id', $validated['vacancy_ids']);
            } elseif (! empty($validated['period_id'])) {
                $vacanciesQuery->where('recruitment_period_id', $validated['period_id']);
            } else {
                // Default ke periode aktif atau semua lowongan jika tidak dispesifikasikan
                $activePeriod = RecruitmentPeriod::where('status', RecruitmentPeriodStatus::OPEN)->first();
                if ($activePeriod) {
                    $vacanciesQuery->where('recruitment_period_id', $activePeriod->id);
                }
            }

            $vacancies = $vacanciesQuery->get();

            foreach ($vacancies as $vacancy) {
                // Hapus tahapan lama yang tidak memiliki relasi critical/histories jika aman,
                // atau sinkronkan berdasarkan sequence order
                $existingStages = $vacancy->selectionStages()->orderBy('sort_order')->get();
                $usedStageIds = DB::table('application_stage_histories')
                    ->whereIn('vacancy_selection_stage_id', $existingStages->pluck('id'))
                    ->pluck('vacancy_selection_stage_id')
                    ->toArray();

                $processedStageIds = [];

                foreach ($stagesData as $idx => $sData) {
                    $stageName = $sData['name'] ?? 'Tahapan Seleksi';
                    $desc = $sData['description'] ?? null;
                    $startAt = ! empty($sData['start_date']) ? Carbon::parse($sData['start_date']) : null;
                    $endAt = ! empty($sData['end_date']) ? Carbon::parse($sData['end_date']) : null;
                    $order = (int) ($sData['sequence_order'] ?? ($idx + 1));

                    $existingStage = $existingStages->get($idx);

                    if ($existingStage) {
                        $existingStage->update([
                            'name' => $stageName,
                            'description' => $desc,
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'sort_order' => $order,
                        ]);
                        $processedStageIds[] = $existingStage->id;
                    } else {
                        $newStage = VacancySelectionStage::create([
                            'job_vacancy_id' => $vacancy->id,
                            'name' => $stageName,
                            'description' => $desc,
                            'start_at' => $startAt,
                            'end_at' => $endAt,
                            'sort_order' => $order,
                        ]);
                        $processedStageIds[] = $newStage->id;
                    }
                }

                // Bersihkan stage berlebih jika ada dan belum dipakai
                foreach ($existingStages as $existing) {
                    if (! in_array($existing->id, $processedStageIds, true)) {
                        if (! in_array($existing->id, $usedStageIds, true)) {
                            $existing->delete();
                        }
                    }
                }
            }
        });

        return back()->with('success', "Template '{$template->name}' berhasil diterapkan ke lowongan.");
    }
}
