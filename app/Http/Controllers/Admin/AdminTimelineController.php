<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecruitmentPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\RecruitmentPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminTimelineController extends Controller
{
    /**
     * Tampilkan manajemen periode & timeline seleksi.
     */
    public function index(): View
    {
        $periods = RecruitmentPeriod::with(['vacancies.selectionStages'])
            ->latest('registration_start_at')
            ->get();

        return view('admin.timelines.index', [
            'periods' => $periods,
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

        $isActive = $request->has('is_active');

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

        $isActive = $request->has('is_active');

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
}
