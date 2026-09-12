<?php

namespace App\Services\Recruitment;

use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class RecruitmentService
{
    /**
     * Memeriksa apakah saat ini terdapat lowongan/rekrutmen aktif untuk publik.
     * Digunakan oleh website utama untuk mengaktifkan/menonaktifkan menu REKRUTMEN.
     */
    public function hasActiveRecruitment(?CarbonInterface $at = null): bool
    {
        return JobVacancy::hasActiveRecruitment($at);
    }

    /**
     * Mendapatkan periode rekrutmen aktif saat ini.
     */
    public function getActivePeriod(?CarbonInterface $at = null): ?RecruitmentPeriod
    {
        return RecruitmentPeriod::currentlyOpen($at)->first() ?? RecruitmentPeriod::latest()->first();
    }

    /**
     * Mendapatkan seluruh lowongan pekerjaan yang saat ini terbuka untuk publik.
     */
    public function getCurrentlyOpenVacancies(?CarbonInterface $at = null): Collection
    {
        return JobVacancy::currentlyOpen($at)
            ->with(['recruitmentPeriod', 'qualifications', 'documentRequirements', 'selectionStages'])
            ->get();
    }

    /**
     * Mendapatkan seluruh periode rekrutmen yang saat ini sedang dibuka.
     */
    public function getCurrentlyOpenPeriods(?CarbonInterface $at = null): Collection
    {
        return RecruitmentPeriod::currentlyOpen($at)
            ->with(['jobVacancies' => fn ($query) => $query->currentlyOpen($at)])
            ->get();
    }
}
